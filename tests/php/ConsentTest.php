<?php
/**
 * Consent and analytics: GTM ID, page type and the head script.
 *
 * The script's behaviour in the browser (banner, cookie, events) is covered
 * by tests/e2e/consent.spec.mjs.
 *
 * @package PortfolioCore
 */

/**
 * Tests for plugin/inc/consent.php.
 */
class ConsentTest extends WP_UnitTestCase {

	/**
	 * Sets the GTM ID the plugin reads.
	 *
	 * @param string $id Container ID.
	 */
	private function gtm( $id ) {
		add_filter(
			'portfolio_core_gtm_id',
			static function () use ( $id ) {
				return $id;
			}
		);
	}

	/**
	 * The head script as printed.
	 *
	 * @return string
	 */
	private function head_script() {
		ob_start();
		portfolio_core_print_consent_script();
		return (string) ob_get_clean();
	}

	/**
	 * The config JSON inside the head script.
	 *
	 * @return array
	 */
	private function printed_config() {
		preg_match( '/window\.portfolioConsentConfig = (\{.*?\});/', $this->head_script(), $match );
		return json_decode( $match[1] ?? '', true );
	}

	public function test_without_a_gtm_id_nothing_loads() {
		$this->assertSame( '', portfolio_core_gtm_id() );
		$this->assertSame( '', $this->printed_config()['gtm'] );
	}

	public function test_a_valid_gtm_id_is_kept_in_capitals() {
		$this->gtm( ' gtm-ab12cd3 ' );

		$this->assertSame( 'GTM-AB12CD3', portfolio_core_gtm_id() );
	}

	public function test_anything_else_is_not_a_gtm_id() {
		foreach ( array( 'G-AB12CD3', 'GTM-', 'GTM-AB12"><script>', 'UA-1234-1' ) as $id ) {
			remove_all_filters( 'portfolio_core_gtm_id' );
			$this->gtm( $id );
			$this->assertSame( '', portfolio_core_gtm_id(), $id );
		}
	}

	public function test_the_head_script_denies_everything_by_default() {
		$script = $this->head_script();

		$this->assertStringStartsWith( '<script', $script );
		$this->assertStringContainsString( "gtag( 'consent', 'default'", $script );
		$this->assertMatchesRegularExpression( "/analytics_storage: 'denied'/", $script );
		$this->assertStringNotContainsString( 'googletagmanager.com/gtm.js?id=GTM', $script, 'GTM is loaded by the script after consent, never by the markup.' );
	}

	public function test_the_config_carries_version_retention_and_page() {
		$this->gtm( 'GTM-AB12CD3' );
		$this->go_to( get_permalink( self::factory()->post->create() ) );

		$config = $this->printed_config();

		$this->assertSame( PORTFOLIO_CORE_CONSENT_VERSION, $config['version'] );
		$this->assertSame( 365, $config['days'] );
		$this->assertSame( 'GTM-AB12CD3', $config['gtm'] );
		$this->assertSame( 'note', $config['pageType'] );
	}

	public function test_without_polylang_there_are_no_service_slugs_to_map() {
		$this->assertSame( array(), portfolio_core_service_slugs() );
		$this->assertStringContainsString( '"services":{}', $this->head_script(), 'An object, so the script can look slugs up.' );
	}

	/**
	 * Page type cases.
	 *
	 * @return array<string, array>
	 */
	public function data_page_type() {
		return array(
			'note'    => array( array( 'post_type' => 'post' ), 'note' ),
			'project' => array( array( 'post_type' => 'project' ), 'project' ),
			'service' => array( array( 'post_type' => 'service' ), 'service' ),
			'page'    => array( array( 'post_type' => 'page' ), 'page' ),
		);
	}

	/**
	 * Checks the page type of a single post.
	 *
	 * @dataProvider data_page_type
	 *
	 * @param array  $post     Post to create.
	 * @param string $expected Page type.
	 */
	public function test_page_type( $post, $expected ) {
		$this->go_to( get_permalink( self::factory()->post->create( $post ) ) );

		$this->assertSame( $expected, portfolio_core_page_type() );
	}

	public function test_a_page_template_names_the_page_type() {
		$id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_post_meta( $id, '_wp_page_template', 'templates/contact.php' );
		$this->go_to( get_permalink( $id ) );

		$this->assertSame( 'contact', portfolio_core_page_type() );
	}

	public function test_home_and_404() {
		$home = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );

		$this->go_to( home_url( '/' ) );
		$this->assertSame( 'home', portfolio_core_page_type() );

		$this->go_to( home_url( '/?p=999999' ) );
		$this->assertSame( '404', portfolio_core_page_type() );
	}
}
