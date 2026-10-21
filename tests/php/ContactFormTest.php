<?php
/**
 * Contact form endpoint: POST /wp-json/portfolio/v1/contact.
 *
 * Cloudflare is never called: siteverify is answered by a pre_http_request
 * filter, so the tests run offline and can make Turnstile pass or fail.
 *
 * @package PortfolioCore
 */

/**
 * Tests for plugin/inc/contact-form.php.
 */
class ContactFormTest extends WP_UnitTestCase {

	/**
	 * What the fake siteverify answers; null: no call expected.
	 *
	 * @var array|null
	 */
	private $siteverify = array( 'success' => true );

	/**
	 * Calls made to siteverify.
	 *
	 * @var int
	 */
	private $siteverify_calls = 0;

	/**
	 * Fresh REST server, a fixed visitor IP and a fake siteverify.
	 */
	public function set_up() {
		parent::set_up();

		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );

		$_SERVER['REMOTE_ADDR'] = '203.0.113.7';
		add_filter( 'pre_http_request', array( $this, 'fake_siteverify' ), 10, 3 );
	}

	/**
	 * Removes the REST server.
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tear_down();
	}

	/**
	 * Answers siteverify with $this->siteverify; lets other requests through.
	 *
	 * @param false|array $preempt Response to short-circuit with.
	 * @param array       $args    Request arguments.
	 * @param string      $url     Request URL.
	 * @return false|array
	 */
	public function fake_siteverify( $preempt, $args, $url ) {
		if ( false === strpos( $url, 'turnstile/v0/siteverify' ) ) {
			return $preempt;
		}

		++$this->siteverify_calls;

		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $this->siteverify ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
		);
	}

	/**
	 * Posts to the endpoint.
	 *
	 * @param array $params Body parameters.
	 * @return WP_REST_Response
	 */
	private function post( $params ) {
		$request = new WP_REST_Request( 'POST', '/portfolio/v1/contact' );
		$request->set_body_params( $params );

		return rest_do_request( $request );
	}

	/**
	 * A valid Contact submission.
	 *
	 * @param array $changes Parameters to change or add.
	 * @return array
	 */
	private function contact( $changes = array() ) {
		return array_merge(
			array(
				'form_id'               => 'contact',
				'name'                  => 'Ada Lovelace',
				'email'                 => 'ada@example.com',
				'message'               => 'A new site for a small engine company.',
				'cf-turnstile-response' => 'token',
			),
			$changes
		);
	}

	/**
	 * Messages stored so far.
	 *
	 * @return WP_Post[]
	 */
	private function stored() {
		return get_posts(
			array(
				'post_type'   => PORTFOLIO_CORE_MESSAGE_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
			)
		);
	}

	public function test_an_unknown_form_is_refused() {
		$response = $this->post( $this->contact( array( 'form_id' => 'newsletter' ) ) );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'unknown_form', $response->get_data()['code'] );
	}

	public function test_a_filled_honeypot_gets_a_fake_success_and_nothing_is_stored() {
		$response = $this->post( $this->contact( array( 'website' => 'https://spam.example' ) ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( 'ok' => true ), $response->get_data() );
		$this->assertSame( array(), $this->stored() );
		$this->assertSame( 0, $this->siteverify_calls );
	}

	public function test_without_turnstile_keys_the_form_is_off() {
		add_filter( 'portfolio_core_turnstile_keys', '__return_null' );

		$response = $this->post( $this->contact() );

		$this->assertSame( 503, $response->get_status() );
		$this->assertSame( 'not_configured', $response->get_data()['code'] );
		$this->assertSame( '', portfolio_core_turnstile_site_key() );
	}

	public function test_invalid_fields_come_back_with_a_code_each() {
		$response = $this->post(
			array(
				'form_id' => 'contact',
				'email'   => 'not-an-email',
				'message' => 'Too short',
			)
		);

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'invalid_fields', $response->get_data()['code'] );
		$this->assertSame(
			array(
				'name'    => 'required',
				'email'   => 'invalid',
				'message' => 'too_short',
			),
			$response->get_data()['data']['fields']
		);
		// Fields are checked before Turnstile: a bad form costs no call.
		$this->assertSame( 0, $this->siteverify_calls );
	}

	public function test_a_failed_spam_check_stores_nothing() {
		$this->siteverify = array( 'success' => false );

		$response = $this->post( $this->contact() );

		$this->assertSame( 403, $response->get_status() );
		$this->assertSame( 'challenge_failed', $response->get_data()['code'] );
		$this->assertSame( array(), $this->stored() );
	}

	public function test_a_missing_token_fails_without_calling_cloudflare() {
		$response = $this->post( $this->contact( array( 'cf-turnstile-response' => '' ) ) );

		$this->assertSame( 403, $response->get_status() );
		$this->assertSame( 0, $this->siteverify_calls );
	}

	public function test_real_keys_also_check_the_host_and_the_form() {
		add_filter(
			'portfolio_core_turnstile_keys',
			static function () {
				return array(
					'site'   => 'site',
					'secret' => 'secret',
					'test'   => false,
				);
			}
		);

		$this->siteverify = array(
			'success'  => true,
			'hostname' => 'example.org',
			'action'   => 'services_request',
		);
		$this->assertSame( 403, $this->post( $this->contact() )->get_status(), 'A token issued for another form.' );

		$this->siteverify = array(
			'success'  => true,
			'hostname' => 'evil.example',
			'action'   => 'contact',
		);
		$this->assertSame( 403, $this->post( $this->contact() )->get_status(), 'A token issued on another site.' );

		$this->siteverify = array(
			'success'  => true,
			'hostname' => 'example.org',
			'action'   => 'contact',
		);
		$this->assertSame( 200, $this->post( $this->contact() )->get_status() );
	}

	public function test_an_accepted_message_is_stored_and_emailed() {
		reset_phpmailer_instance();

		$response = $this->post(
			$this->contact(
				array(
					'company' => 'Analytical Engines',
					'lang'    => 'xx',
					'page'    => 'https://evil.example/contact/',
					'extra'   => 'dropped',
				)
			)
		);

		$this->assertSame( 200, $response->get_status() );

		$stored = $this->stored();
		$this->assertCount( 1, $stored );

		$message = $stored[0];
		$this->assertSame( 'private', $message->post_status );
		$this->assertSame( 'Ada Lovelace', $message->post_title );
		$this->assertSame( 'A new site for a small engine company.', $message->post_content );
		$this->assertSame( 'ada@example.com', get_post_meta( $message->ID, '_portfolio_email', true ) );
		$this->assertSame( 'Analytical Engines', get_post_meta( $message->ID, '_portfolio_company', true ) );
		$this->assertSame( 'contact', get_post_meta( $message->ID, '_portfolio_form_id', true ) );
		$this->assertSame( '', get_post_meta( $message->ID, '_portfolio_page', true ), 'A page on another host is dropped.' );
		$this->assertSame( '', get_post_meta( $message->ID, '_portfolio_extra', true ), 'A field the form does not define is dropped.' );
		$this->assertSame( '', get_post_meta( $message->ID, '_portfolio_ip', true ), 'The IP is never stored.' );
		$this->assertSame( '1', get_post_meta( $message->ID, '_portfolio_mail_sent', true ) );

		$mail = tests_retrieve_phpmailer_instance()->get_sent();
		$this->assertSame( get_option( 'admin_email' ), $mail->to[0][0] );
		$this->assertStringContainsString( 'Reply-To: ada@example.com', $mail->header );
		$this->assertStringContainsString( 'A new site for a small engine company.', $mail->body );
	}

	public function test_the_sixth_attempt_in_an_hour_is_refused() {
		for ( $i = 1; $i <= 5; $i++ ) {
			$this->assertSame( 200, $this->post( $this->contact() )->get_status(), "Attempt $i." );
		}

		$response = $this->post( $this->contact() );
		$this->assertSame( 429, $response->get_status() );
		$this->assertSame( 'rate_limited', $response->get_data()['code'] );

		// Another visitor is not affected.
		$_SERVER['REMOTE_ADDR'] = '203.0.113.8';
		$this->assertSame( 200, $this->post( $this->contact() )->get_status() );
	}

	public function test_invalid_attempts_count_against_the_limit() {
		for ( $i = 1; $i <= 5; $i++ ) {
			$this->post( array( 'form_id' => 'contact' ) );
		}

		$this->assertSame( 429, $this->post( $this->contact() )->get_status() );
	}

	/**
	 * Validation cases: field => value => expected error code, or null when
	 * the value is accepted.
	 *
	 * @return array<string, array>
	 */
	public function data_validation() {
		return array(
			'name too long'       => array( 'name', str_repeat( 'a', 101 ), 'too_long' ),
			'name at the limit'   => array( 'name', str_repeat( 'a', 100 ), null ),
			'email without @'     => array( 'email', 'ada.example.com', 'invalid' ),
			'message too short'   => array( 'message', 'Hello', 'too_short' ),
			'message too long'    => array( 'message', str_repeat( 'a', 5001 ), 'too_long' ),
			'site without dot'    => array( 'site', 'localhost', 'invalid' ),
			'site with a scheme'  => array( 'site', 'ftp://example.com', 'invalid' ),
			'site as people type' => array( 'site', 'example.com', null ),
			'service not sure'    => array( 'service', 'not-sure', null ),
			'unknown service'     => array( 'service', 'web-design', 'invalid' ),
		);
	}

	/**
	 * Checks one field of the request form.
	 *
	 * @dataProvider data_validation
	 *
	 * @param string      $field    Field name.
	 * @param string      $value    Value sent.
	 * @param string|null $expected Error code, or null when accepted.
	 */
	public function test_validation( $field, $value, $expected ) {
		$params = array_merge(
			array(
				'name'    => 'Ada',
				'email'   => 'ada@example.com',
				'service' => 'not-sure',
				'message' => 'A new site for a small engine company.',
			),
			array( $field => $value )
		);

		$result = portfolio_core_contact_validate( portfolio_core_contact_forms()['services_request'], $params );

		$this->assertSame( $expected, $result['errors'][ $field ] ?? null );
	}

	public function test_a_site_typed_without_a_scheme_gets_https() {
		$this->assertSame( 'https://example.com', portfolio_core_contact_clean_url( 'example.com' ) );
		$this->assertSame( 'http://example.com/a', portfolio_core_contact_clean_url( 'http://example.com/a' ) );
	}

	public function test_a_published_service_is_a_valid_choice_a_draft_is_not() {
		self::factory()->post->create(
			array(
				'post_type' => 'service',
				'post_name' => 'wordpress-maintenance',
			)
		);
		self::factory()->post->create(
			array(
				'post_type'   => 'service',
				'post_name'   => 'coming-soon',
				'post_status' => 'draft',
			)
		);

		$this->assertTrue( portfolio_core_contact_is_service( 'wordpress-maintenance' ) );
		$this->assertFalse( portfolio_core_contact_is_service( 'coming-soon' ) );
	}

	public function test_markup_in_a_message_is_stripped_and_line_breaks_kept() {
		$result = portfolio_core_contact_validate(
			portfolio_core_contact_forms()['contact'],
			array(
				'name'    => '<b>Ada</b>',
				'email'   => 'ada@example.com',
				'message' => "First line <script>alert(1)</script>\nSecond line",
			)
		);

		$this->assertSame( array(), $result['errors'] );
		$this->assertSame( 'Ada', $result['data']['name'] );
		$this->assertSame( "First line \nSecond line", $result['data']['message'] );
	}
}
