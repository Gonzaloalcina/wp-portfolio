<?php
/**
 * Contact messages: storage, email and retention.
 *
 * @package PortfolioCore
 */

/**
 * Tests for plugin/inc/contact-messages.php.
 */
class ContactMessagesTest extends WP_UnitTestCase {

	/**
	 * Stores a message as the endpoint would.
	 *
	 * @return int Post ID.
	 */
	private function store() {
		return portfolio_core_contact_store(
			array(
				'name'    => 'Ada Lovelace',
				'email'   => 'ada@example.com',
				'service' => 'not-sure',
				'message' => 'A new site.',
			),
			array(
				'form_id' => 'services_request',
				'lang'    => 'en',
				'page'    => '',
			)
		);
	}

	/**
	 * A message dated some months back.
	 *
	 * @param int $months Months ago.
	 * @return int Post ID.
	 */
	private function message_from( $months ) {
		return self::factory()->post->create(
			array(
				'post_type'   => PORTFOLIO_CORE_MESSAGE_TYPE,
				'post_status' => 'private',
				'post_date'   => gmdate( 'Y-m-d H:i:s', strtotime( "-$months months" ) ),
			)
		);
	}

	public function test_nobody_can_add_a_message_in_the_admin() {
		$this->assertSame( 'do_not_allow', get_post_type_object( PORTFOLIO_CORE_MESSAGE_TYPE )->cap->create_posts );
		$this->assertFalse( is_post_type_viewable( PORTFOLIO_CORE_MESSAGE_TYPE ) );
	}

	public function test_the_title_names_the_sender_and_the_service() {
		$this->assertSame( 'Ada Lovelace · not-sure', get_post_field( 'post_title', $this->store() ) );
	}

	public function test_a_failed_email_keeps_the_message_marked_not_emailed() {
		$id = $this->store();
		add_filter( 'pre_wp_mail', '__return_false' );

		$this->assertFalse( portfolio_core_contact_notify( $id ) );
		$this->assertSame( '0', get_post_meta( $id, '_portfolio_mail_sent', true ) );
		$this->assertNotNull( get_post( $id ) );
	}

	public function test_messages_older_than_twelve_months_are_deleted_for_good() {
		$old    = $this->message_from( 13 );
		$recent = $this->message_from( 11 );

		$this->assertSame( 1, portfolio_core_contact_cleanup() );
		$this->assertNull( get_post( $old ), 'Deleted, not trashed.' );
		$this->assertNotNull( get_post( $recent ) );
	}

	public function test_the_retention_period_can_be_changed() {
		$id = $this->message_from( 4 );
		add_filter(
			'portfolio_core_contact_retention_months',
			static function () {
				return 3;
			}
		);

		portfolio_core_contact_cleanup();

		$this->assertNull( get_post( $id ) );
	}

	public function test_the_cleanup_runs_daily() {
		portfolio_core_schedule_contact_cleanup();

		$this->assertSame( 'daily', wp_get_schedule( PORTFOLIO_CORE_CONTACT_CLEANUP_HOOK ) );
	}
}
