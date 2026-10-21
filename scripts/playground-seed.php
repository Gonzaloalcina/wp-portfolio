<?php
/**
 * Optional local seed for Playground: the phase-1 pages with their
 * templates, and Lorem projects, services and notes, so every template can
 * be previewed, in English and in Spanish. Not part of the public blueprint.
 *
 * Usage: SEED=1 nvm exec 24 npm run playground
 *
 * Everything here is placeholder text. Page and service names follow the
 * sitemap; no fact about Gonzalo or his clients is stated.
 *
 * Every item exists in both languages and is linked as a translation (see
 * docs/decisions/11-i18n.md). The Spanish ones are created after the English
 * ones. Without Polylang and a Spanish language the seed stays English only.
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

// Spanish too, when Polylang has the language.
$pll = function_exists( 'pll_save_post_translations' ) && in_array( 'es', pll_languages_list(), true );

/**
 * Creates a published post and returns its ID.
 *
 * @param array $args wp_insert_post() arguments.
 * @return int
 */
$create = static fn( $args ) => (int) wp_insert_post( $args + array( 'post_status' => 'publish' ) );

/**
 * Creates the Spanish counterpart of an English post and links the pair.
 * The Spanish slug is given: Polylang free cannot share a slug between
 * languages, so a slug that equals the English one carries the "-es" suffix
 * (portfolio-core hides it in the public URL).
 *
 * @param int   $en_id English post ID.
 * @param array $args  wp_insert_post() arguments of the Spanish post.
 * @return int Spanish post ID, 0 without Spanish.
 */
$translate = static function ( $en_id, $args ) use ( $create, $pll ) {
	if ( ! $pll ) {
		return 0;
	}

	$es_id = $create( $args + array( 'post_type' => get_post_type( $en_id ) ) );
	pll_set_post_language( $en_id, 'en' );
	pll_set_post_language( $es_id, 'es' );
	pll_save_post_translations(
		array(
			'en' => $en_id,
			'es' => $es_id,
		)
	);

	return $es_id;
};

// Home: a static front page with the contact fields the other pages reuse.
$home_id = $create(
	array(
		'post_type'  => 'page',
		'post_title' => 'Home',
		'post_name'  => 'home',
	)
);
$home_ids = array( 'en' => $home_id );
if ( $pll ) {
	$home_ids['es'] = $translate(
		$home_id,
		array(
			'post_title' => 'Inicio',
			'post_name'  => 'inicio',
		)
	);
}

foreach ( $home_ids as $id ) {
	update_field( 'field_portfolio_home_cta_label', 'Lorem ipsum dolor', $id );
	update_field( 'field_portfolio_home_contact_text', $lorem, $id );
	update_field(
		'field_portfolio_home_testimonials',
		array(
			array(
				'quote' => $lorem,
				'name'  => 'Lorem Ipsum',
				'role'  => 'Dolor sit, Amet',
			),
			array(
				'quote' => $lorem_line,
				'name'  => 'Dolor Amet',
				'role'  => 'Consectetur, Adipiscing',
			),
		),
		$id
	);
}

// Site settings: one field per language (`_en`, `_es`), Polylang free does not translate options pages.
update_field( 'field_portfolio_core_settings_name', 'Gonzalo Alcina', 'option' );
update_field( 'field_portfolio_core_settings_role_en', 'Lorem ipsum developer', 'option' );
update_field( 'field_portfolio_core_settings_role_es', 'Desarrollador lorem ipsum', 'option' );
update_field( 'field_portfolio_core_settings_bio_en', $lorem, 'option' );
update_field( 'field_portfolio_core_settings_bio_es', $lorem, 'option' );
update_field( 'field_portfolio_core_settings_location', 'Lorem, Ipsum', 'option' );
update_field( 'field_portfolio_core_settings_location_es', 'Lorem, Ipsum', 'option' );
update_field( 'field_portfolio_core_settings_languages_en', 'Lorem (native), Ipsum (professional)', 'option' );
update_field( 'field_portfolio_core_settings_languages_es', 'Lorem (nativo), Ipsum (profesional)', 'option' );
update_field(
	'field_portfolio_core_settings_profile_links',
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
	'option'
);

// Pages: slug => English title, template ('' = page.php), Spanish title and
// slug. Spanish slugs follow the sitemap; Cookies has the same slug in both
// languages, so its Spanish page is saved as cookies-es.
$pages = array(
	'about'                  => array( 'About', 'templates/about.php', 'Sobre mí', 'sobre-mi' ),
	'work'                   => array( 'Work', 'templates/work.php', 'Trabajo', 'trabajo' ),
	'services'               => array( 'Services', 'templates/services.php', 'Servicios', 'servicios' ),
	'notes'                  => array( 'Notes', '', 'Notas', 'notas' ),
	'resume'                 => array( 'Resume', 'templates/resume.php', 'CV', 'cv' ),
	'contact'                => array( 'Contact', 'templates/contact.php', 'Contacto', 'contacto' ),
	'how-this-site-is-built' => array( 'How this site is built', '', 'Cómo está hecha esta web', 'como-esta-hecha-esta-web' ),
	'privacy'                => array( 'Privacy', '', 'Privacidad', 'privacidad' ),
	'cookies'                => array( 'Cookies', '', 'Cookies', 'cookies-es' ),
	'legal-notice'           => array( 'Legal notice', '', 'Aviso legal', 'aviso-legal' ),
);

$page_ids = array(
	'en' => array(),
	'es' => array(),
);
foreach ( $pages as $slug => list( $title, $template, $title_es, $slug_es ) ) {
	$page_args = array(
		'post_content' => in_array( $slug, array( 'notes', 'contact', 'work', 'services', 'resume' ), true ) ? '' : $prose,
	);

	$page_ids['en'][ $slug ] = $create(
		$page_args + array(
			'post_type'  => 'page',
			'post_title' => $title,
			'post_name'  => $slug,
		)
	);

	if ( $pll ) {
		$page_ids['es'][ $slug ] = $translate(
			$page_ids['en'][ $slug ],
			$page_args + array(
				'post_title' => $title_es,
				'post_name'  => $slug_es,
			)
		);
	}

	foreach ( $page_ids as $language_ids ) {
		if ( ! empty( $language_ids[ $slug ] ) ) {
			if ( $template ) {
				update_post_meta( $language_ids[ $slug ], '_wp_page_template', $template );
			}

			update_field( 'field_portfolio_page_intro', $lorem, $language_ids[ $slug ] );
		}
	}
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $page_ids['en']['notes'] );
update_option( 'wp_page_for_privacy_policy', $page_ids['en']['privacy'] );

// Resume section titles, per language.
$resume_titles = array(
	'en' => array( 'Experience', 'Skills', 'Education', 'Languages' ),
	'es' => array( 'Experiencia', 'Competencias', 'Formación', 'Idiomas' ),
);

foreach ( array_filter( $page_ids ) as $language => $language_ids ) {
	// Services: How it works.
	update_field(
		'field_portfolio_services_steps',
		array_map(
			static fn( $title ) => array(
				'title' => $title,
				'text'  => $lorem_line,
			),
			array( 'Lorem', 'Ipsum', 'Dolor', 'Sit amet' )
		),
		$language_ids['services']
	);

	// About: every section filled, so the template can be judged whole.
	update_field( 'field_portfolio_about_currently', 'Lorem ipsum, Dolor', $language_ids['about'] );
	update_field(
		'field_portfolio_about_how',
		array_map(
			static fn( $area ) => array(
				'area' => $area,
				'text' => $lorem_line,
				'who'  => 'Lorem, ipsum',
			),
			array( 'Lorem', 'Ipsum dolor', 'Sit amet' )
		),
		$language_ids['about']
	);
	update_field( 'field_portfolio_about_tools', "{$lorem}\n\n{$lorem}", $language_ids['about'] );
	update_field( 'field_portfolio_about_outside', $lorem, $language_ids['about'] );

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
			$resume_titles[ $language ]
		),
		$language_ids['resume']
	);
}

// Projects: one featured case, this site, two client rows and one without
// consent (must not show anywhere). Each has its Spanish title and, for this
// site, the Spanish page its entry links to.
$projects = array(
	array( 'Founderz', 'founderz', 'not_needed', true, '', 'Founderz', '' ),
	array( 'This website', 'this-website', 'not_needed', false, '/how-this-site-is-built/', 'Esta web', '/es/como-esta-hecha-esta-web/' ),
	array( 'Lorem client', 'lorem-client', 'yes', false, 'https://example.com/', 'Cliente lorem', 'https://example.com/' ),
	array( 'Ipsum agency site', 'ipsum-agency-site', 'yes', false, 'https://example.org/', 'Web de agencia ipsum', 'https://example.org/' ),
	array( 'Hidden pending client', 'hidden-pending-client', 'pending', false, 'https://example.net/', 'Cliente pendiente oculto', 'https://example.net/' ),
);

$project_ids = array(
	'en' => array(),
	'es' => array(),
);
foreach ( $projects as $order => list( $title, $slug, $client_ok, $featured, $url, $title_es, $url_es ) ) {
	$project_args = array(
		'post_content' => $prose,
		'menu_order'   => $order,
	);

	$project_ids['en'][ $slug ] = $create(
		$project_args + array(
			'post_type'  => 'project',
			'post_title' => $title,
			'post_name'  => $slug,
		)
	);

	if ( $pll ) {
		$project_ids['es'][ $slug ] = $translate(
			$project_ids['en'][ $slug ],
			$project_args + array(
				'post_title' => $title_es,
				'post_name'  => "{$slug}-es",
			)
		);
	}

	foreach ( $project_ids as $language => $language_ids ) {
		if ( empty( $language_ids[ $slug ] ) ) {
			continue;
		}

		$id = $language_ids[ $slug ];
		update_field( 'field_portfolio_core_project_client', 'Lorem ipsum', $id );
		update_field( 'field_portfolio_core_project_client_ok', $client_ok, $id );
		update_field( 'field_portfolio_core_project_year', '2026', $id );
		update_field( 'field_portfolio_core_project_type', 'es' === $language ? 'Tipo lorem' : 'Lorem type', $id );
		update_field( 'field_portfolio_core_project_built_with', 'Lorem, ipsum, dolor', $id );
		update_field( 'field_portfolio_core_project_role', 'Lorem ipsum', $id );
		update_field( 'field_portfolio_core_project_what', $lorem_line, $id );
		update_field( 'field_portfolio_core_project_outcome', $lorem, $id );
		update_field( 'field_portfolio_core_project_url', 'es' === $language ? $url_es : $url, $id );
		update_field( 'field_portfolio_core_project_featured', $featured ? 1 : 0, $id );
		// Home: the two with a page on this site above, the rest under Also built.
		update_field( 'field_portfolio_core_project_home', in_array( $slug, array( 'founderz', 'this-website' ), true ) ? 'selected' : 'also_built', $id );
	}
}

// Notes: twelve, so the index paginates (ten per page). The Spanish slug
// differs from the English one, so no "-es" suffix is needed.
$note_ids = array(
	'en' => array(),
	'es' => array(),
);
for ( $i = 1; $i <= 12; $i++ ) {
	$note_args = array(
		'post_excerpt' => $lorem_line,
		'post_content' => "<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">Lorem ipsum dolor</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->",
		'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( "-{$i} weeks" ) ),
	);

	$note_ids['en'][] = $create(
		$note_args + array(
			'post_title' => "Lorem ipsum note {$i}",
			'post_name'  => "lorem-ipsum-note-{$i}",
		)
	);

	if ( $pll ) {
		$note_ids['es'][] = $translate(
			end( $note_ids['en'] ),
			$note_args + array(
				'post_title' => "Nota lorem ipsum {$i}",
				'post_name'  => "nota-lorem-ipsum-{$i}",
			)
		);
	}
}

// Services: the four phase-1 services. Only maintenance has a price and is
// off the Home; one has a situation line, so the hub shows every row type
// (with and without price, situation or the problem line in its place).
// Spanish slugs follow the sitemap.
$services = array(
	array( 'WordPress development', 'wordpress-development', '', 'Lorem ipsum: dolor sit amet, consectetur adipiscing elit.', 'Desarrollo WordPress', 'desarrollo-wordpress', '' ),
	array( 'Website ownership', 'website-ownership', '', '', 'Responsable de web', 'responsable-de-web', '' ),
	array( 'AI and automation', 'ai-automation', '', '', 'IA y automatización', 'ia-y-automatizacion', '' ),
	array( 'WordPress maintenance', 'wordpress-maintenance', 'Lorem 80 €/month', '', 'Mantenimiento WordPress', 'mantenimiento-wordpress', 'Lorem 80 €/mes' ),
);

$service_ids = array(
	'en' => array(),
	'es' => array(),
);
foreach ( $services as $order => list( $title, $slug, $price, $situation, $title_es, $slug_es, $price_es ) ) {
	$service_args = array(
		'post_content' => $prose,
		'menu_order'   => $order,
	);

	$service_ids['en'][ $slug ] = $create(
		$service_args + array(
			'post_type'  => 'service',
			'post_title' => $title,
			'post_name'  => $slug,
		)
	);

	if ( $pll ) {
		$service_ids['es'][ $slug ] = $translate(
			$service_ids['en'][ $slug ],
			$service_args + array(
				'post_title' => $title_es,
				'post_name'  => $slug_es,
			)
		);
	}

	foreach ( $service_ids as $language => $language_ids ) {
		if ( empty( $language_ids[ $slug ] ) ) {
			continue;
		}

		$id = $language_ids[ $slug ];
		update_field( 'field_portfolio_core_service_problem', $lorem_line, $id );
		update_field( 'field_portfolio_core_service_price', 'es' === $language ? $price_es : $price, $id );
		update_field( 'field_portfolio_core_service_situation', $situation, $id );
		update_field( 'field_portfolio_core_service_home', 'wordpress-maintenance' === $slug ? 0 : 1, $id );
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
		// The proof is stored as IDs: each language points at its own items.
		update_field( 'field_portfolio_core_service_proof', array( $project_ids[ $language ]['founderz'], $project_ids[ $language ]['hidden-pending-client'], $note_ids[ $language ][0] ), $id );
	}
}

// A note with the blog blocks (portfolio-core), the newest on the index. The
// Related block stores the English IDs and resolves the translation on render,
// so both languages share the same content.
$blocks_note = array(
	'post_excerpt' => $lorem_line,
	'post_content' => "<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->\n\n"
		. "<!-- wp:portfolio-core/code -->\n<figure class=\"code-a\"><div class=\"code-a__filename\">plugin/inc/blocks.php</div><pre class=\"code-a__pre\" tabindex=\"0\"><code>function portfolio_core_register_blocks() {\n\t\$build = dirname( __DIR__ ) . '/blocks/build';\n\n\twp_register_block_types_from_metadata_collection( \$build, \$build . '/blocks-manifest.php' );\n}\nadd_action( 'init', 'portfolio_core_register_blocks' );</code></pre><figcaption class=\"code-a__caption\">Lorem ipsum dolor sit amet.</figcaption></figure>\n<!-- /wp:portfolio-core/code -->\n\n"
		. "<!-- wp:portfolio-core/aside -->\n<aside class=\"aside-a\"><p class=\"aside-a__label\">Decision</p><div class=\"aside-a__content\"><!-- wp:paragraph -->\n<p>{$lorem_line}</p>\n<!-- /wp:paragraph --></div></aside>\n<!-- /wp:portfolio-core/aside -->\n\n"
		. "<!-- wp:paragraph -->\n<p>{$lorem}</p>\n<!-- /wp:paragraph -->\n\n"
		. '<!-- wp:portfolio-core/related {"serviceId":' . $service_ids['en']['wordpress-development'] . ',"projectId":' . $project_ids['en']['founderz'] . '} /-->',
);

$blocks_note_id = $create(
	$blocks_note + array(
		'post_title' => 'Lorem ipsum blocks',
		'post_name'  => 'lorem-ipsum-blocks',
	)
);
$translate(
	$blocks_note_id,
	$blocks_note + array(
		'post_title' => 'Bloques lorem ipsum',
		'post_name'  => 'lorem-ipsum-blocks-es',
	)
);

// Menus: one per language, with pages as items (not custom links), so they
// follow each language's permalinks and the address of the install. The
// blueprint made "Main" with custom links; here its items become pages.
$main_items = array( 'about', 'work', 'services', 'notes', 'contact' );
$menus      = array();

$main = wp_get_nav_menu_object( 'Main' );
if ( $main ) {
	foreach ( (array) wp_get_nav_menu_items( $main->term_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}
	$menus['en'] = (int) $main->term_id;
} else {
	$menus['en'] = (int) wp_create_nav_menu( 'Main' );
}

if ( $pll ) {
	$menus['es'] = (int) wp_create_nav_menu( 'Main ES' );
}

foreach ( $menus as $language => $menu_id ) {
	foreach ( $main_items as $slug ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_ids[ $language ][ $slug ],
				'menu-item-status'    => 'publish',
			)
		);
	}
}

// Polylang keeps one menu per language and location in its own option (the
// `primary___es` location the Customizer shows is only a label for it). The
// theme mod stays for the default language.
if ( $pll ) {
	$pll_menus = PLL()->options->get( 'nav_menus' );
	$pll_theme = get_option( 'stylesheet' );

	$pll_menus[ $pll_theme ]['primary'] = $menus;
	PLL()->options->set( 'nav_menus', $pll_menus );
	PLL()->options->save();
}
set_theme_mod( 'nav_menu_locations', array( 'primary' => $menus['en'] ) );

flush_rewrite_rules();

// Polylang caches the language list with each language's front page and posts
// page in it. Inside this run it still sees the options as they were at boot,
// so any list built here (the rewrite rules need it) has no front page. Drop
// it last: the first request builds a fresh one, and /es/ shows the Spanish
// Home instead of the blog index.
if ( $pll ) {
	PLL()->model->clean_languages_cache();
}
