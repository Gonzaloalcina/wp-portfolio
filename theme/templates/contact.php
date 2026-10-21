<?php
/**
 * Template Name: Contact
 *
 * Contact: H1 and intro, the contact form, then other ways to reach Gonzalo
 * (the Home's profile links and the resume).
 *
 * The form posts to the portfolio-core endpoint (see inc/forms.php); without
 * the plugin it is a placeholder with a disabled button.
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

$portfolio_connection = portfolio_theme_form_connection( 'contact' );

portfolio_theme_component(
	'form-a',
	$portfolio_connection + array(
		'label'  => __( 'Contact form', 'portfolio-theme' ),
		'fields' => array(
			array(
				'name'         => 'name',
				'label'        => __( 'Name', 'portfolio-theme' ),
				'required'     => true,
				'autocomplete' => 'name',
				'maxlength'    => 100,
			),
			array(
				'name'         => 'email',
				'label'        => __( 'Email', 'portfolio-theme' ),
				'type'         => 'email',
				'required'     => true,
				'autocomplete' => 'email',
				'maxlength'    => 254,
			),
			array(
				'name'         => 'company',
				'label'        => __( 'Company', 'portfolio-theme' ),
				'autocomplete' => 'organization',
				'maxlength'    => 100,
			),
			array(
				'name'      => 'message',
				'label'     => __( 'Message', 'portfolio-theme' ),
				'type'      => 'textarea',
				'required'  => true,
				'maxlength' => 5000,
				'hint'      => __( 'A project: what it is and when it starts.', 'portfolio-theme' ),
			),
		),
		'button' => __( 'Send message', 'portfolio-theme' ),
		'note'   => $portfolio_connection ? '' : __( 'The form is not connected yet.', 'portfolio-theme' ),
		'data'   => array( 'form-id' => 'contact' ),
		// Same link as under the Services request form.
		'more'   => get_privacy_policy_url()
			? array(
				'title' => __( 'How the data you send is used', 'portfolio-theme' ),
				'url'   => get_privacy_policy_url(),
			)
			: array(),
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
						'url'   => portfolio_theme_page_url( '/resume/' ),
					),
				)
			),
		),
	)
);

get_footer();
