<?php
/**
 * Project page: a case study (featured) or any other project, with the
 * same structure; a case study just has more text. H1 (the case title, or
 * the name) with "what I did" as intro, the facts (client, year, role…),
 * the method and decisions from the editor, the outcome, then the closing
 * Contact section.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

$portfolio_case_title = portfolio_theme_field( 'project_case_title' );
?>
<article <?php post_class(); ?>>
	<?php
	portfolio_theme_component( 'breadcrumb-a', array( 'items' => portfolio_theme_breadcrumbs() ) );

	portfolio_theme_component(
		'title-a',
		array(
			'title' => $portfolio_case_title ? $portfolio_case_title : get_the_title(),
			'meta'  => portfolio_theme_field( 'project_featured' ) ? __( 'Case study', 'portfolio-theme' ) : __( 'Project', 'portfolio-theme' ),
			'intro' => portfolio_theme_field( 'project_what' ),
		)
	);

	portfolio_theme_component( 'facts-a', array( 'items' => portfolio_theme_project_facts( get_post() ) ) );

	portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

	portfolio_theme_component(
		'section-a',
		array(
			'id'    => 'outcome',
			'title' => __( 'Outcome', 'portfolio-theme' ),
			'text'  => portfolio_theme_field( 'project_outcome' ),
			'more'  => array(
				'title' => __( 'All work', 'portfolio-theme' ),
				'url'   => portfolio_theme_page_url( '/work/' ),
			),
		)
	);
	?>
</article>
<?php
portfolio_theme_contact_section();

get_footer();
