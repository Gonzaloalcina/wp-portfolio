<?php
/**
 * Closes endpoints the site does not use and that leak information.
 *
 * This is site behaviour rather than presentation, so it moves to the
 * portfolio-core plugin once that plugin exists.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/*
 * XML-RPC: no client (mobile app, Jetpack, pingbacks) uses it, and it is a
 * common target for brute-force login attempts. Disabling authenticated
 * methods is not enough, so every method goes, plus the X-Pingback header.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );

/**
 * Drops the X-Pingback header that points clients to xmlrpc.php.
 *
 * @param array $headers HTTP headers.
 * @return array
 */
function portfolio_theme_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'portfolio_theme_remove_pingback_header' );

/**
 * The REST users endpoint lists every author's login slug to anyone, which
 * hands half of a login to attackers. It stays open to users who can edit
 * posts, because the block editor reads it (author picker). The rest of the
 * REST API is untouched.
 *
 * @param WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Result to send.
 * @param array                                            $handler  Route handler.
 * @param WP_REST_Request                                  $request  Request.
 * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed
 */
function portfolio_theme_restrict_rest_users( $response, $handler, $request ) {
	if ( str_starts_with( $request->get_route(), '/wp/v2/users' ) && ! current_user_can( 'edit_posts' ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to do that.', 'portfolio-theme' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	return $response;
}
add_filter( 'rest_request_before_callbacks', 'portfolio_theme_restrict_rest_users', 10, 3 );
