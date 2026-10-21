<?php
/**
 * SCF field groups for the content types. Registered in code, so they are
 * versioned with the site and identical in every environment.
 *
 * Field names follow the private data hub's columns. A project's status is
 * its post status (draft, published), not a field.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the project and service field groups.
 */
function portfolio_core_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'        => 'group_portfolio_core_project',
			'title'      => __( 'Project', 'portfolio-core' ),
			'location'   => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'project',
					),
				),
			),
			'position'   => 'acf_after_title',
			'menu_order' => 0,
			'fields'     => array(
				array(
					'key'          => 'field_portfolio_core_project_case_title',
					'label'        => __( 'Case title', 'portfolio-core' ),
					'name'         => 'project_case_title',
					'type'         => 'text',
					'instructions' => __( 'The H1 of the project page. Empty: the project name. Lists always show the name.', 'portfolio-core' ),
				),
				array(
					'key'   => 'field_portfolio_core_project_client',
					'label' => __( 'Client', 'portfolio-core' ),
					'name'  => 'project_client',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_portfolio_core_project_client_ok',
					'label'         => __( 'Client OK to name', 'portfolio-core' ),
					'name'          => 'project_client_ok',
					'type'          => 'select',
					'instructions'  => __( 'Only Yes and Not needed are shown on the site.', 'portfolio-core' ),
					'choices'       => array(
						'pending'    => __( 'Pending', 'portfolio-core' ),
						'yes'        => __( 'Yes', 'portfolio-core' ),
						'no'         => __( 'No', 'portfolio-core' ),
						'not_needed' => __( 'Not needed', 'portfolio-core' ),
					),
					'default_value' => 'pending',
				),
				array(
					'key'          => 'field_portfolio_core_project_source',
					'label'        => __( 'Source', 'portfolio-core' ),
					'name'         => 'project_source',
					'type'         => 'text',
					'instructions' => __( 'How the work came in, e.g. employer, agency or direct client.', 'portfolio-core' ),
				),
				array(
					'key'   => 'field_portfolio_core_project_year',
					'label' => __( 'Year', 'portfolio-core' ),
					'name'  => 'project_year',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_portfolio_core_project_type',
					'label'        => __( 'Type', 'portfolio-core' ),
					'name'         => 'project_type',
					'type'         => 'text',
					'instructions' => __( 'e.g. Custom theme, Elementor site.', 'portfolio-core' ),
				),
				array(
					'key'   => 'field_portfolio_core_project_built_with',
					'label' => __( 'Built with', 'portfolio-core' ),
					'name'  => 'project_built_with',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_portfolio_core_project_role',
					'label' => __( 'Role', 'portfolio-core' ),
					'name'  => 'project_role',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_portfolio_core_project_what',
					'label'        => __( 'What I did', 'portfolio-core' ),
					'name'         => 'project_what',
					'type'         => 'text',
					'maxlength'    => 140,
					'instructions' => __( 'One line. Shown in the Work list and under the case title.', 'portfolio-core' ),
				),
				array(
					'key'          => 'field_portfolio_core_project_outcome',
					'label'        => __( 'Outcome', 'portfolio-core' ),
					'name'         => 'project_outcome',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'Qualitative when there are no public figures.', 'portfolio-core' ),
				),
				array(
					'key'          => 'field_portfolio_core_project_url',
					'label'        => __( 'Link', 'portfolio-core' ),
					'name'         => 'project_url',
					'type'         => 'text',
					'instructions' => __( 'The live site, shown in the facts. A page on this site (/how-this-site-is-built/) replaces the project page: lists link there and the project URL redirects.', 'portfolio-core' ),
				),
				array(
					'key'   => 'field_portfolio_core_project_screenshot_ok',
					'label' => __( 'Screenshot allowed', 'portfolio-core' ),
					'name'  => 'project_screenshot_ok',
					'type'  => 'true_false',
					'ui'    => 1,
				),
				array(
					'key'          => 'field_portfolio_core_project_featured',
					'label'        => __( 'Featured case', 'portfolio-core' ),
					'name'         => 'project_featured',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => __( 'A case study: listed under Case studies in the Work hub, with the long text. Every project has a page; this marks the main ones.', 'portfolio-core' ),
				),
				array(
					'key'           => 'field_portfolio_core_project_home',
					'label'         => __( 'Home', 'portfolio-core' ),
					'name'          => 'project_home',
					'type'          => 'select',
					'choices'       => array(
						'selected'   => __( 'Selected work', 'portfolio-core' ),
						'also_built' => __( 'Also built', 'portfolio-core' ),
					),
					'allow_null'    => 1,
					'default_value' => '',
					'return_format' => 'value',
					'instructions'  => __( 'Where the project shows on the Home. Selected work: up to three, with the line. Also built: name and year · type. Empty: not on the Home. Only projects whose client may be named are shown.', 'portfolio-core' ),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_core_service',
			'title'    => __( 'Service', 'portfolio-core' ),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'service',
					),
				),
			),
			'position' => 'acf_after_title',
			'fields'   => array(
				array(
					'key'          => 'field_portfolio_core_service_problem',
					'label'        => __( 'Problem you solve', 'portfolio-core' ),
					'name'         => 'service_problem',
					'type'         => 'text',
					'maxlength'    => 140,
					'instructions' => __( 'One line, in the client\'s words. Shown in lists and under the title.', 'portfolio-core' ),
				),
				array(
					'key'          => 'field_portfolio_core_service_situation',
					'label'        => __( 'When it is the right one', 'portfolio-core' ),
					'name'         => 'service_situation',
					'type'         => 'text',
					'maxlength'    => 140,
					'instructions' => __( 'One line for the Services hub: the situation this service fits. Empty: the hub shows the problem line.', 'portfolio-core' ),
				),
				array(
					'key'          => 'field_portfolio_core_service_price',
					'label'        => __( 'Price', 'portfolio-core' ),
					'name'         => 'service_price',
					'type'         => 'text',
					'maxlength'    => 40,
					'instructions' => __( 'Short, as the hub shows it: "From 80 €/month" or "Quoted". Empty: no price in the hub.', 'portfolio-core' ),
				),
				array(
					'key'          => 'field_portfolio_core_service_includes',
					'label'        => __( 'What it includes', 'portfolio-core' ),
					'name'         => 'service_includes',
					'type'         => 'repeater',
					'layout'       => 'block',
					'button_label' => __( 'Add item', 'portfolio-core' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_portfolio_core_service_includes_title',
							'label'    => __( 'Title', 'portfolio-core' ),
							'name'     => 'title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_portfolio_core_service_includes_text',
							'label'     => __( 'Line', 'portfolio-core' ),
							'name'      => 'text',
							'type'      => 'text',
							'maxlength' => 140,
						),
					),
				),
				array(
					'key'           => 'field_portfolio_core_service_proof',
					'label'         => __( 'Proof', 'portfolio-core' ),
					'name'          => 'service_proof',
					'type'          => 'relationship',
					'instructions'  => __( 'Projects and notes that show this service. Every service links to its proof.', 'portfolio-core' ),
					'post_type'     => array( 'project', 'post' ),
					'filters'       => array( 'search', 'post_type' ),
					'return_format' => 'id',
				),
				array(
					'key'           => 'field_portfolio_core_service_home',
					'label'         => __( 'Show on the Home', 'portfolio-core' ),
					'name'          => 'service_home',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
					'instructions'  => __( 'Off: the service is listed in the Services hub only (e.g. maintenance, linked from the footer).', 'portfolio-core' ),
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_core_register_fields' );
