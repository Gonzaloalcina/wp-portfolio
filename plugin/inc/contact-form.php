<?php
/**
 * Contact form endpoint: POST /wp-json/portfolio/v1/contact.
 *
 * One endpoint for every form on the site (the Contact page and the request
 * form of the Services hub and service pages), told apart by `form_id`.
 * A request goes through, in order:
 *
 * 1. Honeypot: a filled trap field gets a fake success, so a bot learns
 *    nothing.
 * 2. Rate limit per IP (the IP is only kept as a hash, for an hour).
 * 3. Field validation, per form.
 * 4. Cloudflare Turnstile, verified server-side.
 * 5. The message is stored (inc/contact-messages.php), then emailed. A
 *    failed email does not lose it.
 *
 * The response carries codes, not copy: the theme owns the wording in both
 * languages.
 *
 * Keys live in wp-config.php, never in the database or the repo:
 * PORTFOLIO_TURNSTILE_SITE_KEY and PORTFOLIO_TURNSTILE_SECRET_KEY. Without
 * them, local and development installs use Cloudflare's test keys (always
 * pass); any other environment has no form (fail closed).
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

define( 'PORTFOLIO_CORE_CONTACT_NAMESPACE', 'portfolio/v1' );
define( 'PORTFOLIO_CORE_CONTACT_ROUTE', '/contact' );

// Cloudflare's published test keys: the widget and siteverify always pass.
define( 'PORTFOLIO_CORE_TURNSTILE_TEST_SITE_KEY', '1x00000000000000000000AA' );
define( 'PORTFOLIO_CORE_TURNSTILE_TEST_SECRET_KEY', '1x0000000000000000000000000000000AA' );

/**
 * The forms and their fields: name => rules. `required` and `max` (characters)
 * for every field; `type` email, url, service or text; `min` for a message.
 *
 * @return array<string, array<string, array>>
 */
function portfolio_core_contact_forms() {
	$name    = array(
		'required' => true,
		'max'      => 100,
	);
	$email   = array(
		'required' => true,
		'max'      => 254,
		'type'     => 'email',
	);
	$message = array(
		'required' => true,
		'min'      => 10,
		'max'      => 5000,
	);

	return array(
		'contact'          => array(
			'name'    => $name,
			'email'   => $email,
			'company' => array( 'max' => 100 ),
			'message' => $message,
		),
		'services_request' => array(
			'name'    => $name,
			'email'   => $email,
			'service' => array(
				'required' => true,
				'max'      => 100,
				'type'     => 'service',
			),
			'site'    => array(
				'max'  => 200,
				'type' => 'url',
			),
			'message' => $message,
		),
	);
}

/**
 * The Turnstile key pair in use: from wp-config.php, else the test keys on
 * local and development installs, else none.
 *
 * @return array{site: string, secret: string, test: bool}|null
 */
function portfolio_core_turnstile_keys() {
	$keys = null;

	if ( defined( 'PORTFOLIO_TURNSTILE_SITE_KEY' ) && defined( 'PORTFOLIO_TURNSTILE_SECRET_KEY' ) ) {
		$keys = array(
			'site'   => (string) PORTFOLIO_TURNSTILE_SITE_KEY,
			'secret' => (string) PORTFOLIO_TURNSTILE_SECRET_KEY,
			'test'   => false,
		);
	} elseif ( in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
		$keys = array(
			'site'   => PORTFOLIO_CORE_TURNSTILE_TEST_SITE_KEY,
			'secret' => PORTFOLIO_CORE_TURNSTILE_TEST_SECRET_KEY,
			'test'   => true,
		);
	}

	/**
	 * Filters the Turnstile key pair; null means the form is off. Used by the
	 * PHPUnit tests to stand in for an environment without keys.
	 *
	 * @param array|null $keys site, secret and test, or null.
	 */
	return apply_filters( 'portfolio_core_turnstile_keys', $keys );
}

/**
 * Public site key for the widget, empty when the form is not configured.
 * The theme connects its forms only when this is not empty.
 *
 * @return string
 */
function portfolio_core_turnstile_site_key() {
	$keys = portfolio_core_turnstile_keys();

	return $keys ? $keys['site'] : '';
}

/**
 * URL the forms post to.
 *
 * @return string
 */
function portfolio_core_contact_endpoint() {
	return rest_url( PORTFOLIO_CORE_CONTACT_NAMESPACE . PORTFOLIO_CORE_CONTACT_ROUTE );
}

/**
 * Registers the route.
 */
function portfolio_core_register_contact_route() {
	register_rest_route(
		PORTFOLIO_CORE_CONTACT_NAMESPACE,
		PORTFOLIO_CORE_CONTACT_ROUTE,
		array(
			'methods'             => 'POST',
			'callback'            => 'portfolio_core_contact_submit',
			// Public form: anyone may post. Abuse is handled inside.
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'portfolio_core_register_contact_route' );

/**
 * Handles a submission.
 *
 * @param WP_REST_Request $request Request (JSON or form-encoded).
 * @return WP_REST_Response|WP_Error
 */
function portfolio_core_contact_submit( WP_REST_Request $request ) {
	$forms   = portfolio_core_contact_forms();
	$form_id = (string) $request->get_param( 'form_id' );

	if ( ! isset( $forms[ $form_id ] ) ) {
		return new WP_Error( 'unknown_form', 'Unknown form.', array( 'status' => 400 ) );
	}

	// 1. Honeypot. Same answer as a real success.
	if ( '' !== trim( (string) $request->get_param( 'website' ) ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	$keys = portfolio_core_turnstile_keys();
	if ( ! $keys ) {
		return new WP_Error( 'not_configured', 'The form is not configured.', array( 'status' => 503 ) );
	}

	// 2. Rate limit, before anything costly.
	$ip = portfolio_core_contact_client_ip();
	if ( ! portfolio_core_contact_rate_limit_hit( $ip ) ) {
		return new WP_Error( 'rate_limited', 'Too many messages.', array( 'status' => 429 ) );
	}

	// 3. Fields.
	$result = portfolio_core_contact_validate( $forms[ $form_id ], $request->get_params() );
	if ( $result['errors'] ) {
		return new WP_Error(
			'invalid_fields',
			'Some fields are not valid.',
			array(
				'status' => 400,
				'fields' => $result['errors'],
			)
		);
	}

	// 4. Turnstile.
	$token = (string) $request->get_param( 'cf-turnstile-response' );
	if ( ! portfolio_core_turnstile_verify( $token, $ip, $form_id, $keys ) ) {
		return new WP_Error( 'challenge_failed', 'The spam check failed.', array( 'status' => 403 ) );
	}

	// 5. Store, then email.
	$context    = array(
		'form_id' => $form_id,
		'lang'    => portfolio_core_contact_lang( (string) $request->get_param( 'lang' ) ),
		'page'    => portfolio_core_contact_page( (string) $request->get_param( 'page' ) ),
	);
	$message_id = portfolio_core_contact_store( $result['data'], $context );
	if ( ! $message_id ) {
		return new WP_Error( 'not_saved', 'The message could not be saved.', array( 'status' => 500 ) );
	}

	portfolio_core_contact_notify( $message_id );

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/**
 * Validates and cleans a form's fields. Fields the form does not define are
 * dropped.
 *
 * @param array<string, array> $fields Rules of the form (see portfolio_core_contact_forms()).
 * @param array<string, mixed> $params Request parameters.
 * @return array{data: array<string, string>, errors: array<string, string>}
 *         Errors are field => code: required, invalid, too_short, too_long.
 */
function portfolio_core_contact_validate( $fields, $params ) {
	$data   = array();
	$errors = array();

	foreach ( $fields as $name => $rules ) {
		$raw   = isset( $params[ $name ] ) && is_scalar( $params[ $name ] ) ? (string) $params[ $name ] : '';
		$type  = $rules['type'] ?? 'text';
		$value = 'text' === $type && 'message' === $name ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );

		if ( '' === $value ) {
			if ( ! empty( $rules['required'] ) ) {
				$errors[ $name ] = 'required';
			}
			continue;
		}

		$length = mb_strlen( $value );
		if ( $length > $rules['max'] ) {
			$errors[ $name ] = 'too_long';
			continue;
		}
		if ( isset( $rules['min'] ) && $length < $rules['min'] ) {
			$errors[ $name ] = 'too_short';
			continue;
		}

		if ( 'email' === $type ) {
			$value = is_email( $value ) ? sanitize_email( $value ) : '';
		} elseif ( 'url' === $type ) {
			$value = portfolio_core_contact_clean_url( $value );
		} elseif ( 'service' === $type ) {
			$value = portfolio_core_contact_is_service( $value ) ? $value : '';
		}

		if ( '' === $value ) {
			$errors[ $name ] = 'invalid';
			continue;
		}

		$data[ $name ] = $value;
	}

	return array(
		'data'   => $data,
		'errors' => $errors,
	);
}

/**
 * A site URL as people type it: "example.com" is accepted and gets https://.
 *
 * @param string $value Typed URL.
 * @return string Clean http(s) URL, or empty when it is not one.
 */
function portfolio_core_contact_clean_url( $value ) {
	if ( ! preg_match( '#^https?://#i', $value ) ) {
		$value = 'https://' . $value;
	}

	$url  = esc_url_raw( $value, array( 'http', 'https' ) );
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );

	return false !== strpos( $host, '.' ) ? $url : '';
}

/**
 * Tells whether a value is a choice of the request form: a published service
 * in any language, or "not-sure".
 *
 * @param string $slug Value of the select.
 * @return bool
 */
function portfolio_core_contact_is_service( $slug ) {
	if ( 'not-sure' === $slug ) {
		return true;
	}

	$found = get_posts(
		array(
			'post_type'        => 'service',
			'post_status'      => 'publish',
			'name'             => sanitize_title( $slug ),
			'fields'           => 'ids',
			'posts_per_page'   => 1,
			'no_found_rows'    => true,
			'suppress_filters' => false,
			'lang'             => '', // Polylang: any language.
		)
	);

	return ! empty( $found );
}

/**
 * The language a message was written from: a Polylang language slug, else
 * the site's.
 *
 * @param string $lang Slug sent by the form.
 * @return string
 */
function portfolio_core_contact_lang( $lang ) {
	$languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array();

	if ( in_array( $lang, $languages, true ) ) {
		return $lang;
	}

	return function_exists( 'pll_default_language' ) ? (string) pll_default_language() : substr( get_locale(), 0, 2 );
}

/**
 * The page a message was sent from, only when it is on this site.
 *
 * @param string $url URL sent by the form.
 * @return string
 */
function portfolio_core_contact_page( $url ) {
	$url = esc_url_raw( $url, array( 'http', 'https' ) );

	return $url && wp_parse_url( $url, PHP_URL_HOST ) === wp_parse_url( home_url(), PHP_URL_HOST ) ? $url : '';
}

/**
 * The visitor's IP. REMOTE_ADDR only: forwarded headers can be forged, and
 * the site is not behind a proxy. Revisit if a CDN is put in front.
 *
 * @return string
 */
function portfolio_core_contact_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

/**
 * Counts an attempt against the visitor's limit.
 *
 * Five attempts an hour per IP by default (filter
 * `portfolio_core_contact_rate_limit`). The key is a salted hash, so the IP
 * itself is never stored. The window starts with the first attempt.
 *
 * @param string $ip Visitor's IP.
 * @return bool False when the limit is reached.
 */
function portfolio_core_contact_rate_limit_hit( $ip ) {
	$limit = (int) apply_filters( 'portfolio_core_contact_rate_limit', 5 );
	$key   = 'portfolio_contact_' . substr( wp_hash( $ip, 'nonce' ), 0, 32 );
	$entry = get_transient( $key );

	if ( ! is_array( $entry ) || $entry['until'] <= time() ) {
		$entry = array(
			'count' => 0,
			'until' => time() + HOUR_IN_SECONDS,
		);
	}

	if ( $entry['count'] >= $limit ) {
		return false;
	}

	++$entry['count'];
	set_transient( $key, $entry, $entry['until'] - time() );

	return true;
}

/**
 * Verifies a Turnstile token with Cloudflare (siteverify). With real keys,
 * the token must also come from this site's host and from this form (the
 * widget's action is the form ID); the test keys answer for a dummy host
 * and action, so only success is checked.
 *
 * @param string $token   Widget token.
 * @param string $ip      Visitor's IP.
 * @param string $form_id Form the token was issued for.
 * @param array  $keys    Key pair (portfolio_core_turnstile_keys()).
 * @return bool
 */
function portfolio_core_turnstile_verify( $token, $ip, $form_id, $keys ) {
	if ( '' === $token || strlen( $token ) > 2048 ) {
		return false;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 10,
			'body'    => array_filter(
				array(
					'secret'   => $keys['secret'],
					'response' => $token,
					'remoteip' => $ip,
				)
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return false;
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $result['success'] ) ) {
		return false;
	}

	if ( $keys['test'] ) {
		return true;
	}

	return ( $result['hostname'] ?? '' ) === wp_parse_url( home_url(), PHP_URL_HOST )
		&& ( $result['action'] ?? '' ) === $form_id;
}
