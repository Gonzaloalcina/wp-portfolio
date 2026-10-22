<?php
/**
 * Component logo-a: the site mark (a cell with its marker, the grid detail of
 * the design) followed by the site name as text. Goes inside the header link.
 * The mark is decorative: the name is the link's accessible name.
 *
 * Args: name (site name by default), class (extra classes).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_name = $args['name'] ?? get_bloginfo( 'name' );
?>
<span class="<?php echo esc_attr( trim( 'logo-a ' . ( $args['class'] ?? '' ) ) ); ?>">
	<svg class="logo-a__mark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" aria-hidden="true" focusable="false"><rect x="2" y="2" width="60" height="60" fill="none" stroke="currentColor" stroke-width="4"/><rect x="16" y="32" width="16" height="16" fill="currentColor"/></svg>
	<span class="logo-a__name"><?php echo esc_html( $portfolio_name ); ?></span>
</span>
