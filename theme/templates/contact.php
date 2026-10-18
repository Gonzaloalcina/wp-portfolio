<?php
/**
 * Template Name: Contact
 *
 * Contact: H1 and intro, the contact form, then other ways to reach Gonzalo
 * (the Home's profile links and the resume).
 *
 * The form is a static placeholder until the portfolio-core plugin provides
 * its endpoint; its submit button stays disabled until then.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

portfolio_theme_component(
	'hero-a',
	array(
		'title' => get_the_title(),
		'intro' => portfolio_theme_page_intro(),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

portfolio_theme_component(
	'form-a',
	array(
		'label'  => __( 'Contact form', 'portfolio-theme' ),
		'fields' => array(
			array(
				'name'         => 'name',
				'label'        => __( 'Name', 'portfolio-theme' ),
				'required'     => true,
				'autocomplete' => 'name',
			),
			array(
				'name'         => 'email',
				'label'        => __( 'Email', 'portfolio-theme' ),
				'type'         => 'email',
				'required'     => true,
				'autocomplete' => 'email',
			),
			array(
				'name'  => 'company',
				'label' => __( 'Company', 'portfolio-theme' ),
			),
			array(
				'name'     => 'message',
				'label'    => __( 'Message', 'portfolio-theme' ),
				'type'     => 'textarea',
				'required' => true,
				'hint'     => __( 'A role or a project: what it is and when it starts.', 'portfolio-theme' ),
			),
		),
		'button' => __( 'Send message', 'portfolio-theme' ),
		'note'   => __( 'The form is not connected yet.', 'portfolio-theme' ),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'elsewhere',
		'title'   => __( 'Elsewhere', 'portfolio-theme' ),
		'actions' => array(
			'links' => array_merge(
				portfolio_theme_profile_links(),
				array(
					array(
						'title' => __( 'Resume', 'portfolio-theme' ),
						'url'   => home_url( '/resume/' ),
					),
				)
			),
		),
	)
);

get_footer();
