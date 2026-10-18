<?php
/**
 * Single note: H1 with the date, the excerpt as intro, the post content
 * (blocks), then a link back to all notes.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

$portfolio_notes_id = (int) get_option( 'page_for_posts' );
?>
<article <?php post_class(); ?>>
	<?php
	portfolio_theme_component(
		'title-a',
		array(
			'title'    => get_the_title(),
			'meta'     => get_the_date(),
			'datetime' => get_the_date( 'Y-m-d' ),
			'intro'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);

	portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );
	?>
</article>
<?php
if ( $portfolio_notes_id ) {
	portfolio_theme_component(
		'link-row-a',
		array(
			'links' => array(
				array(
					'title' => __( 'All notes', 'portfolio-theme' ),
					'url'   => get_permalink( $portfolio_notes_id ),
				),
			),
			'class' => 'site__back',
		)
	);
}

get_footer();
