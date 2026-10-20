<?php
/**
 * Template for the /design-system page: a living reference of the tokens and
 * base styles, rendered from theme/assets/data/tokens.json.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );

$portfolio_unit    = portfolio_theme_design_token( 'unit' );
$portfolio_spaces  = portfolio_theme_design_tokens_by_prefix( 'space.' );
$portfolio_sizes   = portfolio_theme_design_tokens_by_prefix( 'font.size.' );
$portfolio_layout  = portfolio_theme_design_tokens_by_prefix( 'layout.' );
$portfolio_details = array_merge(
	portfolio_theme_design_tokens_by_prefix( 'font.weight.' ),
	portfolio_theme_design_tokens_by_prefix( 'font.tracking.' ),
	portfolio_theme_design_tokens_by_prefix( 'line.' ),
	portfolio_theme_design_tokens_by_prefix( 'marker.' )
);
$portfolio_colors  = portfolio_theme_design_tokens()['colors'];
$portfolio_paper   = array_column( $portfolio_colors, null, 'name' )['paper'] ?? null;
?>

<article <?php post_class( 'design-system' ); ?>>
	<header>
		<h1><?php the_title(); ?></h1>
		<p class="meta"><?php esc_html_e( 'Living reference, rendered from the design tokens. Every value on this page comes from tokens/tokens.json.', 'portfolio-theme' ); ?></p>
	</header>

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

	<section class="design-system__section" aria-labelledby="ds-unit">
		<h2 id="ds-unit"><?php esc_html_e( 'Unit and spacing', 'portfolio-theme' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %s: unit value, e.g. 0.5rem (8px). */
				esc_html__( 'Every size derives from one unit, %s, in steps of 0.5. Sizes are in rem, so they follow the font size the visitor sets in the browser. The only exceptions are 1px hairlines and heading tracking.', 'portfolio-theme' ),
				'<code>' . esc_html( portfolio_theme_format_dimension( $portfolio_unit['value'] ?? '' ) ) . '</code>'
			);
			?>
		</p>

		<table class="design-system__table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Token', 'portfolio-theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Unit ×', 'portfolio-theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Value', 'portfolio-theme' ); ?></th>
					<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Sample', 'portfolio-theme' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $portfolio_spaces as $portfolio_token ) : ?>
					<tr>
						<td><code><?php echo esc_html( $portfolio_token['var'] ); ?></code></td>
						<td><?php echo esc_html( $portfolio_token['multiplier'] ); ?></td>
						<td><?php echo esc_html( portfolio_theme_format_dimension( $portfolio_token['value'] ) ); ?></td>
						<td><span class="design-system__bar" style="<?php echo esc_attr( 'width: var(' . $portfolio_token['var'] . ')' ); ?>"></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section class="design-system__section" aria-labelledby="ds-type">
		<h2 id="ds-type"><?php esc_html_e( 'Typography', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'Newsreader for headings, Inter for text. Both self-hosted. Font sizes are multiples of a quarter unit; line heights are whole units, so all text sits on the 8px grid.', 'portfolio-theme' ); ?></p>

		<div class="design-system__specimens">
			<?php foreach ( array_reverse( $portfolio_sizes ) as $portfolio_token ) : ?>
				<?php
				$portfolio_step        = substr( $portfolio_token['path'], strlen( 'font.size.' ) );
				$portfolio_line_height = portfolio_theme_design_token( 'font.line-height.' . $portfolio_step );
				$portfolio_is_heading  = in_array( $portfolio_step, array( 'xl', 'lg' ), true );
				$portfolio_style       = sprintf(
					'font-size: var(%1$s); line-height: var(%2$s);',
					$portfolio_token['var'],
					$portfolio_line_height['var'] ?? 'normal'
				);
				?>
				<div class="design-system__specimen">
					<p class="meta">
						<?php
						printf(
							'%1$s · %2$s / %3$s · %4$s',
							esc_html( $portfolio_step ),
							esc_html( portfolio_theme_format_dimension( $portfolio_token['value'] ) ),
							esc_html( portfolio_theme_format_dimension( $portfolio_line_height['value'] ?? '' ) ),
							esc_html( $portfolio_is_heading ? 'Newsreader' : 'Inter' )
						);
						?>
					</p>
					<p class="<?php echo esc_attr( $portfolio_is_heading ? 'design-system__heading' : '' ); ?>" style="<?php echo esc_attr( $portfolio_style ); ?>">
						<?php esc_html_e( 'Campaigns ship on time when the system is clear.', 'portfolio-theme' ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="design-system__section" aria-labelledby="ds-color">
		<h2 id="ds-color"><?php esc_html_e( 'Colour', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'One paper, one ink, one grey. Light is the reference theme; dark follows the system or the toggle. Contrast is computed against paper (WCAG 2).', 'portfolio-theme' ); ?></p>

		<table class="design-system__table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Token', 'portfolio-theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Light', 'portfolio-theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Dark', 'portfolio-theme' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $portfolio_colors as $portfolio_color ) : ?>
					<tr>
						<td><code><?php echo esc_html( $portfolio_color['var'] ); ?></code></td>
						<?php foreach ( array( 'light', 'dark' ) as $portfolio_mode ) : ?>
							<td>
								<span class="design-system__swatch" style="<?php echo esc_attr( 'background: ' . $portfolio_color[ $portfolio_mode ] ); ?>"></span>
								<?php echo esc_html( $portfolio_color[ $portfolio_mode ] ); ?>
								<?php if ( $portfolio_paper && 'paper' !== $portfolio_color['name'] ) : ?>
									<span class="meta">
										<?php
										printf(
											/* translators: %s: contrast ratio, e.g. 16.4. */
											esc_html__( '%s:1', 'portfolio-theme' ),
											esc_html( number_format( portfolio_theme_contrast_ratio( $portfolio_color[ $portfolio_mode ], $portfolio_paper[ $portfolio_mode ] ), 1 ) )
										);
										?>
									</span>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				<?php foreach ( portfolio_theme_design_tokens()['derived'] as $portfolio_derived ) : ?>
					<tr>
						<td><code><?php echo esc_html( $portfolio_derived['var'] ); ?></code></td>
						<td colspan="2">
							<span class="design-system__swatch" style="<?php echo esc_attr( 'background: var(' . $portfolio_derived['var'] . ')' ); ?>"></span>
							<code><?php echo esc_html( $portfolio_derived['css'] ); ?></code>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section class="design-system__section" aria-labelledby="ds-theme">
		<h2 id="ds-theme"><?php esc_html_e( 'Light and dark', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'The site follows the system by default. The toggle in the header switches between light and dark; a choice that differs from the system is stored in this browser and applied before the first paint.', 'portfolio-theme' ); ?></p>
		<p class="meta">
			<?php esc_html_e( 'Current:', 'portfolio-theme' ); ?>
			<output data-theme-current><?php esc_html_e( 'requires JavaScript', 'portfolio-theme' ); ?></output>
		</p>
	</section>

	<section class="design-system__section" aria-labelledby="ds-icons">
		<h2 id="ds-icons"><?php esc_html_e( 'Icons', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'Lucide icons on a 24-unit grid, with a thinner stroke. They take the text colour and are decorative: the text next to them carries the meaning. Editors pick them in a reusable icon field.', 'portfolio-theme' ); ?></p>

		<ul class="design-system__icons" role="list">
			<?php foreach ( portfolio_theme_icon_names() as $portfolio_icon ) : ?>
				<li class="design-system__icon">
					<?php portfolio_theme_icon( $portfolio_icon ); ?>
					<span class="meta"><?php echo esc_html( $portfolio_icon ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php $portfolio_card_icon = function_exists( 'get_field' ) ? get_field( 'demo_card_icon' ) : ''; ?>
		<div class="demo-card">
			<?php portfolio_theme_icon( $portfolio_card_icon ? $portfolio_card_icon : 'blocks' ); ?>
			<p class="demo-card__title"><?php esc_html_e( 'Demo card', 'portfolio-theme' ); ?></p>
			<p class="meta">
				<?php
				printf(
					/* translators: %s: icon name. */
					esc_html__( 'Icon picked in the editor: %s', 'portfolio-theme' ),
					esc_html( $portfolio_card_icon ? $portfolio_card_icon : __( 'none (fallback: blocks)', 'portfolio-theme' ) )
				);
				?>
			</p>
		</div>
	</section>

	<section class="design-system__section" aria-labelledby="ds-details">
		<h2 id="ds-details"><?php esc_html_e( 'Lines and details', 'portfolio-theme' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %s: sample link. */
				esc_html__( 'Links are underlined and darken on hover and focus, like %s.', 'portfolio-theme' ),
				'<a href="#ds-details">' . esc_html__( 'this one', 'portfolio-theme' ) . '</a>'
			);
			?>
		</p>
		<ul>
			<li><?php esc_html_e( 'Lists use a small square marker.', 'portfolio-theme' ); ?></li>
			<li><?php esc_html_e( 'Hierarchy comes from tone, weight and spacing.', 'portfolio-theme' ); ?></li>
			<li><?php esc_html_e( 'No gradients, no shadows.', 'portfolio-theme' ); ?></li>
		</ul>
		<hr>
		<p class="meta"><?php esc_html_e( 'Meta text: dates, labels. Small and muted.', 'portfolio-theme' ); ?></p>

		<table class="design-system__table">
			<tbody>
				<?php foreach ( $portfolio_details as $portfolio_token ) : ?>
					<tr>
						<td><code><?php echo esc_html( $portfolio_token['var'] ); ?></code></td>
						<td><?php echo esc_html( 'dimension' === $portfolio_token['type'] ? portfolio_theme_format_dimension( $portfolio_token['value'] ) : $portfolio_token['css'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section class="design-system__section" aria-labelledby="ds-components">
		<h2 id="ds-components"><?php esc_html_e( 'Components', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'Components are named by what they are plus a letter for the design (hero-a, row-a…), never by the page that uses them. A new letter is a different design; a numeric modifier (--second) is a small variation of the same one. Each page loads only the CSS of the components it uses.', 'portfolio-theme' ); ?></p>

		<table class="design-system__table">
			<tbody>
				<?php
				$portfolio_component_notes = array(
					'hero-a'       => __( 'H1, short intro, actions-a and a square image. One per page.', 'portfolio-theme' ),
					'section-a'    => __( 'H2, then any of: a paragraph, content components (each with an optional H3), an actions-a and an "All …" link.', 'portfolio-theme' ),
					'list-a'       => __( 'Rows separated by 1px lines, with the square marker.', 'portfolio-theme' ),
					'row-a'        => __( 'Title, optional meta and one optional line.', 'portfolio-theme' ),
					'actions-a'    => __( 'One button-a followed by a link-row-a.', 'portfolio-theme' ),
					'button-a'     => __( 'The primary action. One per page, shown at most twice.', 'portfolio-theme' ),
					'link-row-a'   => __( 'Quiet links on one line.', 'portfolio-theme' ),
					'quote-list-a' => __( 'quote-a items separated by 1px lines, on the same columns as a list-a.', 'portfolio-theme' ),
					'quote-a'      => __( 'A testimonial: the quote, then name (linked to proof) and role.', 'portfolio-theme' ),
					'nav-a'        => __( 'A WordPress menu as quiet links; a "Menu" dropdown on narrow screens. In the header.', 'portfolio-theme' ),
					'title-a'      => __( 'H1 of a text page, with an optional meta line and intro. hero-a is for pages that lead to an action.', 'portfolio-theme' ),
					'prose-a'      => __( 'Long text from the editor, on the text measure.', 'portfolio-theme' ),
					'facts-a'      => __( 'Label and value pairs on 1px lines: client, year, role.', 'portfolio-theme' ),
					'form-a'       => __( 'field-a items, a submit button-a and a note.', 'portfolio-theme' ),
					'field-a'      => __( 'Label, optional hint and a full-width control with a muted line (3:1 contrast).', 'portfolio-theme' ),
					'pagination-a' => __( 'Newer and older links with "Page x of y". Hidden with one page.', 'portfolio-theme' ),
				);
				foreach ( $portfolio_component_notes as $portfolio_component => $portfolio_note ) :
					?>
					<tr>
						<td><code><?php echo esc_html( $portfolio_component ); ?></code></td>
						<td><?php echo esc_html( $portfolio_note ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'list-a and row-a', 'portfolio-theme' ); ?></h3>
		<p><?php esc_html_e( 'Narrow screens stack each row; from 48rem the rows sit in three columns that the whole list shares.', 'portfolio-theme' ); ?></p>
		<?php
		portfolio_theme_component(
			'list-a',
			array(
				'items' => array(
					array(
						'title' => __( 'Row with meta and text', 'portfolio-theme' ),
						'url'   => '#ds-components',
						'meta'  => __( 'Meta · sm, muted', 'portfolio-theme' ),
						'text'  => __( 'One line of text, 140 characters at most. It wraps under itself, never under the title.', 'portfolio-theme' ),
					),
					array(
						'title' => __( 'Row with text only', 'portfolio-theme' ),
						'url'   => '#ds-components',
						'text'  => __( 'Services use this form: a name and the problem it solves.', 'portfolio-theme' ),
					),
					array(
						'title'    => __( 'Row with a date', 'portfolio-theme' ),
						'url'      => '#ds-components',
						'meta'     => wp_date( get_option( 'date_format' ) ),
						'datetime' => wp_date( 'Y-m-d' ),
					),
				),
				'class' => 'alignwide',
			)
		);
		?>

		<h3><?php esc_html_e( 'quote-list-a and quote-a', 'portfolio-theme' ); ?></h3>
		<p><?php esc_html_e( 'Testimonials. The name links to the proof, such as a LinkedIn recommendation. No review schema: Google does not show stars for reviews a site publishes about itself.', 'portfolio-theme' ); ?></p>
		<?php
		portfolio_theme_component(
			'quote-list-a',
			array(
				'items' => array(
					array(
						'quote' => __( 'The quote, in the heading serif at the large size. 240 characters at most, in the words of the person who said it.', 'portfolio-theme' ),
						'name'  => __( 'Name of the person', 'portfolio-theme' ),
						'role'  => __( 'Role, Company', 'portfolio-theme' ),
						'url'   => '#ds-components',
					),
				),
				'class' => 'alignwide',
			)
		);
		?>

		<h3><?php esc_html_e( 'actions-a, button-a and link-row-a', 'portfolio-theme' ); ?></h3>
		<p><?php esc_html_e( 'Ink on paper, inverted on hover and focus, square corners, no shadow. Everything else is a quiet link.', 'portfolio-theme' ); ?></p>
		<?php
		portfolio_theme_component(
			'actions-a',
			array(
				'button' => array(
					'title' => __( 'Primary action', 'portfolio-theme' ),
					'url'   => '#ds-components',
				),
				'links'  => array(
					array(
						'title' => __( 'Quiet link', 'portfolio-theme' ),
						'url'   => '#ds-components',
					),
					array(
						'title' => __( 'Another', 'portfolio-theme' ),
						'url'   => '#ds-components',
					),
				),
			)
		);
		?>
		<h3><?php esc_html_e( 'breadcrumb-a', 'portfolio-theme' ); ?></h3>
		<p><?php esc_html_e( 'Above the H1 of cases, services and notes. The same trail as the BreadcrumbList schema.', 'portfolio-theme' ); ?></p>
		<?php
		portfolio_theme_component(
			'breadcrumb-a',
			array(
				'items' => array(
					array(
						'title' => __( 'Home', 'portfolio-theme' ),
						'url'   => home_url( '/' ),
					),
					array(
						'title' => __( 'Work', 'portfolio-theme' ),
						'url'   => home_url( '/work/' ),
					),
					array(
						'title' => __( 'Case study', 'portfolio-theme' ),
						'url'   => '',
					),
				),
			)
		);
		?>
		<h3><?php esc_html_e( 'title-a', 'portfolio-theme' ); ?></h3>
		<p><?php esc_html_e( 'Notes, case studies and legal pages open with it. Shown here without the H1 element, so this page keeps one H1.', 'portfolio-theme' ); ?></p>
		<div class="title-a">
			<p class="title-a__title design-system__heading" style="font-size: var(--font-size-xl); line-height: var(--font-line-height-xl);"><?php esc_html_e( 'Title of a text page', 'portfolio-theme' ); ?></p>
			<p class="title-a__meta"><time datetime="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( wp_date( get_option( 'date_format' ) ) ); ?></time></p>
			<p class="title-a__intro"><?php esc_html_e( 'One or two sentences of intro, 160 characters at most.', 'portfolio-theme' ); ?></p>
		</div>

		<h3><?php esc_html_e( 'facts-a', 'portfolio-theme' ); ?></h3>
		<?php
		portfolio_theme_component(
			'facts-a',
			array(
				'items' => array(
					array(
						'label' => __( 'Client', 'portfolio-theme' ),
						'value' => __( 'Client name', 'portfolio-theme' ),
					),
					array(
						'label' => __( 'Year', 'portfolio-theme' ),
						'value' => wp_date( 'Y' ),
					),
					array(
						'label' => __( 'Site', 'portfolio-theme' ),
						'value' => 'example.com',
						'url'   => '#ds-components',
					),
				),
			)
		);
		?>

		<h3><?php esc_html_e( 'prose-a', 'portfolio-theme' ); ?></h3>
		<?php
		portfolio_theme_component(
			'prose-a',
			array(
				'content' => '<p>' . esc_html__( 'Text written in the editor. Paragraphs, lists, links and headings take the base styles; quotes, captions, code and tables get their own.', 'portfolio-theme' ) . '</p>'
					. '<blockquote><p>' . esc_html__( 'A quote, in the heading serif, set in a little. No side line.', 'portfolio-theme' ) . '</p></blockquote>'
					. '<table><thead><tr><th>' . esc_html__( 'Column', 'portfolio-theme' ) . '</th><th>' . esc_html__( 'Column', 'portfolio-theme' ) . '</th></tr></thead><tbody><tr><td>' . esc_html__( 'Rows on 1px lines', 'portfolio-theme' ) . '</td><td>2026</td></tr></tbody></table>',
			)
		);
		?>

		<h3><?php esc_html_e( 'form-a and field-a', 'portfolio-theme' ); ?></h3>
		<p><?php esc_html_e( 'Static for now: without an action the submit is disabled. The contact form logic will come from the portfolio-core plugin.', 'portfolio-theme' ); ?></p>
		<?php
		portfolio_theme_component(
			'form-a',
			array(
				'label'  => __( 'Demo form', 'portfolio-theme' ),
				'fields' => array(
					array(
						'name'     => 'demo-email',
						'label'    => __( 'Email', 'portfolio-theme' ),
						'type'     => 'email',
						'required' => true,
					),
					array(
						'name'  => 'demo-message',
						'label' => __( 'Message', 'portfolio-theme' ),
						'type'  => 'textarea',
						'hint'  => __( 'A hint under the label.', 'portfolio-theme' ),
					),
				),
				'button' => __( 'Send message', 'portfolio-theme' ),
				'note'   => __( 'A note under the button.', 'portfolio-theme' ),
			)
		);
		?>

		<h3><?php esc_html_e( 'pagination-a', 'portfolio-theme' ); ?></h3>
		<?php
		portfolio_theme_component(
			'pagination-a',
			array(
				'prev'    => array(
					'title' => __( 'Newer notes', 'portfolio-theme' ),
					'url'   => '#ds-components',
				),
				'next'    => array(
					'title' => __( 'Older notes', 'portfolio-theme' ),
					'url'   => '#ds-components',
				),
				'current' => 2,
				'total'   => 3,
				'label'   => __( 'Demo pagination', 'portfolio-theme' ),
			)
		);
		?>
	</section>

	<section class="design-system__section" aria-labelledby="ds-layout">
		<h2 id="ds-layout"><?php esc_html_e( 'Layout', 'portfolio-theme' ); ?></h2>
		<table class="design-system__table">
			<tbody>
				<?php foreach ( $portfolio_layout as $portfolio_token ) : ?>
					<tr>
						<td><code><?php echo esc_html( $portfolio_token['var'] ); ?></code></td>
						<td><?php echo esc_html( $portfolio_token['multiplier'] ); ?> ×</td>
						<td><?php echo esc_html( portfolio_theme_format_dimension( $portfolio_token['value'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</article>

<?php
get_footer();
