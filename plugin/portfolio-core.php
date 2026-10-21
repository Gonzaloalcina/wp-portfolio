<?php
/**
 * Plugin Name:       Portfolio Core
 * Plugin URI:        https://gonzaloalcina.dev
 * Description:       Site functionality for gonzaloalcina.dev that must survive a theme change: content types, their fields, the blog blocks, site settings, the contact form and comments off.
 * Version:           0.1.0
 * Requires at least: 7.1
 * Requires PHP:      8.0
 * Author:            Gonzalo Alcina
 * Author URI:        https://gonzaloalcina.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       portfolio-core
 * Domain Path:       /languages
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/content-types.php';
require_once __DIR__ . '/inc/fields.php';
require_once __DIR__ . '/inc/blocks.php';
require_once __DIR__ . '/inc/comments.php';
require_once __DIR__ . '/inc/settings.php';
require_once __DIR__ . '/inc/i18n.php';
require_once __DIR__ . '/inc/contact-form.php';
require_once __DIR__ . '/inc/contact-messages.php';

/**
 * Registers the content types' rewrite rules on activation, so their URLs
 * work without visiting Settings › Permalinks.
 */
function portfolio_core_activate() {
	portfolio_core_register_content_types();
	portfolio_core_add_rewrite_rules();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'portfolio_core_activate' );

/**
 * Drops the content types' rewrite rules and the messages' retention job on
 * deactivation.
 */
function portfolio_core_deactivate() {
	wp_clear_scheduled_hook( PORTFOLIO_CORE_CONTACT_CLEANUP_HOOK );
	unregister_post_type( 'project' );
	unregister_post_type( 'service' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'portfolio_core_deactivate' );
