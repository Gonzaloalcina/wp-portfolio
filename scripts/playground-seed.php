<?php
/**
 * Optional local seed for Playground: the phase-1 pages with their
 * templates, and Lorem projects, services and notes, so every template can
 * be previewed. Not part of the public blueprint.
 *
 * Usage: SEED=1 nvm exec 24 npm run playground
 *
 * Everything here is placeholder text. Page and service names follow the
 * sitemap; no fact about Gonzalo or his clients is stated.
 *
 * @package PortfolioTheme
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Runs once in a throwaway Playground instance.

require '/wordpress/wp-load.php';

$lorem      = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$lorem_line = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.';
$prose      = "<p>{$lorem} Ut enim ad minim veniam, quis nostrud <a href=\"#\">exercitation ullamco</a> laboris nisi ut aliquip ex ea commodo consequat.</p>\n"
	. "<h2>Lorem ipsum dolor</h2>\n<p>{$lorem}</p>\n"
	. "<ul><li>Lorem ipsum dolor sit amet</li><li>Consectetur adipiscing elit</li><li>Sed do eiusmod tempor</li></ul>\n"
	. "<h2>Duis aute irure</h2>\n<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>\n"
	. "<blockquote><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt.</p></blockquote>\n"
	. "<p>{$lorem}</p>";

/**
 * Creates a published post and returns its ID.
 *
 * @param array $args wp_insert_post() arguments.
 * @return int
 */
$create = static fn( $args ) => (int) wp_insert_post( $args + array( 'post_status' => 'publish' ) );

// Home: a static front page with the contact fields the other pages reuse.
$home_id = $create(
	array(
		'post_type'  => 'page',
		'post_title' => 'Home',
		'post_name'  => 'home',
	)
);
update_field( 'field_portfolio_home_cta_label', 'Lorem ipsum dolor', $home_id );
update_field( 'field_portfolio_home_contact_text', $lorem, $home_id );
update_field(
	'field_portfolio_home_profile_links',
	array(
		array(
			'title'      => 'GitHub',
			'url'        => 'https://github.com/Gonzaloalcina',
			'is_profile' => 1,
		),
		array(
			'title'      => 'LinkedIn',
			'url'        => '#',
			'is_profile' => 1,
		),
	),
	$home_id
);

// Pages, with the template each one uses ('' = page.php).
$pages = array(
	'about'                  => array( 'About', 'templates/about.php' ),
	'work'                   => array( 'Work', 'templates/work.php' ),
	'services'               => array( 'Services', 'templates/services.php' ),
	'notes'                  => array( 'Notes', '' ),
	'resume'                 => array( 'Resume', 'templates/resume.php' ),
	'contact'                => array( 'Contact', 'templates/contact.php' ),
	'how-this-site-is-built' => array( 'How this site is built', '' ),
	'privacy'                => array( 'Privacy', '' ),
	'cookies'                => array( 'Cookies', '' ),
	'legal-notice'           => array( 'Legal notice', '' ),
);

$page_ids = array();
foreach ( $pages as $slug => list( $title, $template ) ) {
	$page_ids[ $slug ] = $create(
		array(
			'post_type'    => 'page',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => in_array( $slug, array( 'notes', 'contact', 'work', 'services', 'resume' ), true ) ? '' : $prose,
		)
	);

	if ( $template ) {
		update_post_meta( $page_ids[ $slug ], '_wp_page_template', $template );
	}

	update_field( 'field_portfolio_page_intro', $lorem, $page_ids[ $slug ] );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $page_ids['notes'] );

update_field( 'field_portfolio_resume_pdf', '', $page_ids['resume'] );
update_field(
	'field_portfolio_resume_sections',
	array_map(
		static fn( $section ) => array(
			'title' => $section,
			'rows'  => array(
				array(
					'title' => 'Lorem ipsum dolor',
					'meta'  => '2024 – 2026',
					'text'  => $lorem_line,
				),
				array(
					'title' => 'Consectetur adipiscing',
					'meta'  => '2020 – 2024',
					'text'  => $lorem_line,
				),
			),
		),
		array( 'Experience', 'Skills', 'Education', 'Languages' )
	),
	$page_ids['resume']
);

// Projects: one featured case, this site, two client rows and one without
// consent (must not show anywhere).
$projects = array(
	array( 'Founderz', 'founderz', 'not_needed', true, '' ),
	array( 'This website', 'this-website', 'not_needed', false, '/how-this-site-is-built/' ),
	array( 'Lorem client', 'lorem-client', 'yes', false, 'https://example.com/' ),
	array( 'Ipsum agency site', 'ipsum-agency-site', 'yes', false, 'https://example.org/' ),
	array( 'Hidden pending client', 'hidden-pending-client', 'pending', false, 'https://example.net/' ),
);

$project_ids = array();
foreach ( $projects as $order => list( $title, $slug, $client_ok, $featured, $url ) ) {
	$id = $create(
		array(
			'post_type'    => 'project',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $prose,
			'menu_order'   => $order,
		)
	);

	update_field( 'field_portfolio_core_project_client', 'Lorem ipsum', $id );
	update_field( 'field_portfolio_core_project_client_ok', $client_ok, $id );
	update_field( 'field_portfolio_core_project_year', '2026', $id );
	update_field( 'field_portfolio_core_project_type', 'Lorem type', $id );
	update_field( 'field_portfolio_core_project_built_with', 'Lorem, ipsum, dolor', $id );
	update_field( 'field_portfolio_core_project_role', 'Lorem ipsum', $id );
	update_field( 'field_portfolio_core_project_what', $lorem_line, $id );
	update_field( 'field_portfolio_core_project_outcome', $lorem, $id );
	update_field( 'field_portfolio_core_project_url', $url, $id );
	update_field( 'field_portfolio_core_project_featured', $featured ? 1 : 0, $id );

	$project_ids[ $slug ] = $id;
}

// Notes: twelve, so the index paginates (ten per page).
$note_ids = array();
for ( $i = 1; $i <= 12; $i++ ) {
	$note_ids[] = $create(
		array(
			'post_type'    => 'post',
			'post_title'   => "Lorem ipsum note {$i}",
			'post_name'    => "lorem-ipsum-note-{$i}",
			'post_excerpt' => $lorem_line,
			'post_content' => "<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Lorem ipsum dolor</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->",
			'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( "-{$i} weeks" ) ),
		)
	);
}

// Services: the two phase-1 services; AI and automation stays a draft.
$services = array(
	array( 'WordPress development', 'wordpress-development', 'publish' ),
	array( 'Website ownership', 'website-ownership', 'publish' ),
	array( 'AI and automation', 'ai-automation', 'draft' ),
);

$service_ids = array();
foreach ( $services as $order => list( $title, $slug, $status ) ) {
	$id = (int) wp_insert_post(
		array(
			'post_type'    => 'service',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => $status,
			'post_content' => $prose,
			'menu_order'   => $order,
		)
	);

	update_field( 'field_portfolio_core_service_problem', $lorem_line, $id );
	update_field(
		'field_portfolio_core_service_includes',
		array(
			array(
				'title' => 'Lorem ipsum',
				'text'  => $lorem_line,
			),
			array(
				'title' => 'Dolor sit amet',
				'text'  => $lorem_line,
			),
			array(
				'title' => 'Consectetur',
				'text'  => $lorem_line,
			),
		),
		$id
	);
	update_field( 'field_portfolio_core_service_proof', array( $project_ids['founderz'], $project_ids['hidden-pending-client'], $note_ids[0] ), $id );

	$service_ids[ $slug ] = $id;
}

// A note with the blog blocks (portfolio-core), the newest on the index.
$create(
	array(
		'post_type'    => 'post',
		'post_title'   => 'Lorem ipsum blocks',
		'post_name'    => 'lorem-ipsum-blocks',
		'post_excerpt' => $lorem_line,
		'post_content' => "<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->\n\n"
			. "<!-- wp:portfolio-core/code -->\n<figure class=\"code-a\"><div class=\"code-a__filename\">plugin/inc/blocks.php</div><pre class=\"code-a__pre\" tabindex=\"0\"><code>function portfolio_core_register_blocks() {\n\t\$build = dirname( __DIR__ ) . '/blocks/build';\n\n\twp_register_block_types_from_metadata_collection( \$build, \$build . '/blocks-manifest.php' );\n}\nadd_action( 'init', 'portfolio_core_register_blocks' );</code></pre><figcaption class=\"code-a__caption\">Lorem ipsum dolor sit amet.</figcaption></figure>\n<!-- /wp:portfolio-core/code -->\n\n"
			. "<!-- wp:portfolio-core/aside -->\n<aside class=\"aside-a\"><p class=\"aside-a__label\">Decision</p><div class=\"aside-a__content\"><!-- wp:paragraph -->\n<p>{$lorem_line}</p>\n<!-- /wp:paragraph --></div></aside>\n<!-- /wp:portfolio-core/aside -->\n\n"
			. "<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->\n\n"
			. '<!-- wp:portfolio-core/related {"serviceId":' . $service_ids['wordpress-development'] . ',"projectId":' . $project_ids['founderz'] . '} /-->',
	)
);

flush_rewrite_rules();
