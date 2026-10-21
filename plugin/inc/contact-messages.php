<?php
/**
 * Contact messages: every message the forms accept is kept as a private
 * `portfolio_message` post, then emailed to the site's admin address
 * (Settings › General). If the email fails, as it can on shared hosting,
 * the message is still in the admin, marked "not emailed".
 *
 * Messages are read-only in the admin (no editor, nobody can add one) and
 * are deleted for good after 12 months (filter
 * `portfolio_core_contact_retention_months`), by a daily cron job. The
 * privacy page must say so.
 *
 * Nothing is sent to the visitor: an automatic reply could be used to send
 * mail to third parties from this domain.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

define( 'PORTFOLIO_CORE_MESSAGE_TYPE', 'portfolio_message' );
define( 'PORTFOLIO_CORE_CONTACT_CLEANUP_HOOK', 'portfolio_core_contact_cleanup' );

/**
 * Registers the message post type: admin only, not public, not creatable.
 */
function portfolio_core_register_message_type() {
	register_post_type(
		PORTFOLIO_CORE_MESSAGE_TYPE,
		array(
			'labels'          => array(
				'name'          => __( 'Messages', 'portfolio-core' ),
				'singular_name' => __( 'Message', 'portfolio-core' ),
				'edit_item'     => __( 'Message', 'portfolio-core' ),
				'all_items'     => __( 'Messages', 'portfolio-core' ),
				'search_items'  => __( 'Search messages', 'portfolio-core' ),
				'not_found'     => __( 'No messages yet.', 'portfolio-core' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => false,
			'menu_position'   => 25,
			'menu_icon'       => 'dashicons-email',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'portfolio_core_register_message_type' );

/**
 * Stores an accepted message.
 *
 * @param array<string, string> $data    Clean fields (portfolio_core_contact_validate()).
 * @param array<string, string> $context form_id, lang and page.
 * @return int Post ID, 0 on failure.
 */
function portfolio_core_contact_store( $data, $context ) {
	$title = $data['name'];
	if ( ! empty( $data['service'] ) ) {
		$title .= ' · ' . $data['service'];
	}

	$meta = array( '_portfolio_mail_sent' => '0' );
	foreach ( array_merge( $data, $context ) as $key => $value ) {
		if ( 'message' !== $key ) {
			$meta[ '_portfolio_' . $key ] = $value;
		}
	}

	$id = wp_insert_post(
		array(
			'post_type'    => PORTFOLIO_CORE_MESSAGE_TYPE,
			'post_status'  => 'private',
			'post_title'   => $title,
			'post_content' => $data['message'],
			'meta_input'   => $meta,
		),
		true
	);

	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * The labelled fields of a stored message, in reading order.
 *
 * @param int $id Message post ID.
 * @return array<string, string> Label => value, empty values left out.
 */
function portfolio_core_contact_fields( $id ) {
	$labels = array(
		'name'    => __( 'Name', 'portfolio-core' ),
		'email'   => __( 'Email', 'portfolio-core' ),
		'company' => __( 'Company', 'portfolio-core' ),
		'service' => __( 'Service', 'portfolio-core' ),
		'site'    => __( 'Site', 'portfolio-core' ),
		'form_id' => __( 'Form', 'portfolio-core' ),
		'lang'    => __( 'Language', 'portfolio-core' ),
		'page'    => __( 'Sent from', 'portfolio-core' ),
	);

	$fields = array();
	foreach ( $labels as $key => $label ) {
		$value = (string) get_post_meta( $id, '_portfolio_' . $key, true );
		if ( '' !== $value ) {
			$fields[ $label ] = $value;
		}
	}

	return $fields;
}

/**
 * Emails a stored message to the admin address, Reply-To the sender, and
 * marks it as emailed when wp_mail() accepts it.
 *
 * @param int $id Message post ID.
 * @return bool
 */
function portfolio_core_contact_notify( $id ) {
	$email = (string) get_post_meta( $id, '_portfolio_email', true );
	$name  = (string) get_post_meta( $id, '_portfolio_name', true );

	$body = '';
	foreach ( portfolio_core_contact_fields( $id ) as $label => $value ) {
		$body .= $label . ': ' . $value . "\n";
	}
	$body .= "\n" . get_post_field( 'post_content', $id ) . "\n\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) . "\n";

	$sent = wp_mail(
		get_option( 'admin_email' ),
		sprintf(
			/* translators: 1: site name, 2: sender's name. */
			__( '[%1$s] New message from %2$s', 'portfolio-core' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			$name
		),
		$body,
		// The address passed is_email() and sanitize_email(): no line breaks.
		array( 'Reply-To: ' . $email )
	);

	if ( $sent ) {
		update_post_meta( $id, '_portfolio_mail_sent', '1' );
	}

	return $sent;
}

/**
 * Admin list columns: sender, email, form and whether it was emailed.
 *
 * @param array<string, string> $columns Default columns.
 * @return array<string, string>
 */
function portfolio_core_message_columns( $columns ) {
	return array(
		'cb'               => $columns['cb'],
		'title'            => __( 'From', 'portfolio-core' ),
		'portfolio_email'  => __( 'Email', 'portfolio-core' ),
		'portfolio_form'   => __( 'Form', 'portfolio-core' ),
		'portfolio_mailed' => __( 'Emailed', 'portfolio-core' ),
		'date'             => $columns['date'],
	);
}
add_filter( 'manage_' . PORTFOLIO_CORE_MESSAGE_TYPE . '_posts_columns', 'portfolio_core_message_columns' );

/**
 * Prints the custom columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Message post ID.
 */
function portfolio_core_message_column( $column, $post_id ) {
	if ( 'portfolio_email' === $column ) {
		$email = (string) get_post_meta( $post_id, '_portfolio_email', true );
		echo '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';
	} elseif ( 'portfolio_form' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_portfolio_form_id', true ) );
	} elseif ( 'portfolio_mailed' === $column ) {
		echo '1' === get_post_meta( $post_id, '_portfolio_mail_sent', true )
			? esc_html__( 'Yes', 'portfolio-core' )
			: '<strong>' . esc_html__( 'No', 'portfolio-core' ) . '</strong>';
	}
}
add_action( 'manage_' . PORTFOLIO_CORE_MESSAGE_TYPE . '_posts_custom_column', 'portfolio_core_message_column', 10, 2 );

/**
 * Shows the message on its edit screen, read-only.
 */
function portfolio_core_message_meta_box() {
	add_meta_box(
		'portfolio-message',
		__( 'Message', 'portfolio-core' ),
		'portfolio_core_render_message_meta_box',
		PORTFOLIO_CORE_MESSAGE_TYPE,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_' . PORTFOLIO_CORE_MESSAGE_TYPE, 'portfolio_core_message_meta_box' );

/**
 * Renders the message meta box.
 *
 * @param WP_Post $post Message.
 */
function portfolio_core_render_message_meta_box( $post ) {
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( portfolio_core_contact_fields( $post->ID ) as $label => $value ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
	}
	echo '</tbody></table>';
	echo wp_kses_post( wpautop( esc_html( $post->post_content ) ) );
}

/**
 * Schedules the daily retention job if it is not scheduled yet. Runs on
 * init as well as on activation: the plugin is symlinked and may already be
 * active.
 */
function portfolio_core_schedule_contact_cleanup() {
	if ( ! wp_next_scheduled( PORTFOLIO_CORE_CONTACT_CLEANUP_HOOK ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', PORTFOLIO_CORE_CONTACT_CLEANUP_HOOK );
	}
}
add_action( 'init', 'portfolio_core_schedule_contact_cleanup' );

/**
 * Deletes, for good, the messages older than the retention period.
 *
 * @return int Messages deleted.
 */
function portfolio_core_contact_cleanup() {
	$months = max( 1, (int) apply_filters( 'portfolio_core_contact_retention_months', 12 ) );

	$old = get_posts(
		array(
			'post_type'      => PORTFOLIO_CORE_MESSAGE_TYPE,
			'post_status'    => 'any',
			'date_query'     => array( array( 'before' => $months . ' months ago' ) ),
			'fields'         => 'ids',
			'posts_per_page' => 100,
			'no_found_rows'  => true,
		)
	);

	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}

	return count( $old );
}
add_action( PORTFOLIO_CORE_CONTACT_CLEANUP_HOOK, 'portfolio_core_contact_cleanup' );
