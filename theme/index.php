<?php
/**
 * Fallback template.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php the_title( '<h1><a href="' . esc_url( get_permalink() ) . '">', '</a></h1>' ); ?>
			<?php the_content(); ?>
		</article>
		<?php
	endwhile;

	the_posts_pagination();
else :
	?>
	<p><?php esc_html_e( 'Nothing found.', 'portfolio-theme' ); ?></p>
	<?php
endif;

get_footer();
