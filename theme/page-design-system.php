<?php
/**
 * Template for the /design-system page: a living reference of the tokens and
 * base styles, rendered from theme/assets/data/tokens.json.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

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

	<section aria-labelledby="ds-unit">
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

		<table class="ds-table">
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
						<td><span class="ds-bar" style="<?php echo esc_attr( 'width: var(' . $portfolio_token['var'] . ')' ); ?>"></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section aria-labelledby="ds-type">
		<h2 id="ds-type"><?php esc_html_e( 'Typography', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'Newsreader for headings, Inter for text. Both self-hosted. Font sizes are multiples of a quarter unit; line heights are whole units, so all text sits on the 8px grid.', 'portfolio-theme' ); ?></p>

		<div class="ds-specimens">
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
				<div class="ds-specimen">
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
					<p class="<?php echo esc_attr( $portfolio_is_heading ? 'ds-heading' : '' ); ?>" style="<?php echo esc_attr( $portfolio_style ); ?>">
						<?php esc_html_e( 'Campaigns ship on time when the system is clear.', 'portfolio-theme' ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section aria-labelledby="ds-color">
		<h2 id="ds-color"><?php esc_html_e( 'Colour', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'One paper, one ink, one grey. Light is the reference theme; dark follows the system or the toggle. Contrast is computed against paper (WCAG 2).', 'portfolio-theme' ); ?></p>

		<table class="ds-table">
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
								<span class="ds-swatch" style="<?php echo esc_attr( 'background: ' . $portfolio_color[ $portfolio_mode ] ); ?>"></span>
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
							<span class="ds-swatch" style="<?php echo esc_attr( 'background: var(' . $portfolio_derived['var'] . ')' ); ?>"></span>
							<code><?php echo esc_html( $portfolio_derived['css'] ); ?></code>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>

	<section aria-labelledby="ds-theme">
		<h2 id="ds-theme"><?php esc_html_e( 'Light and dark', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'The site follows the system by default. The toggle in the header switches between light and dark; a choice that differs from the system is stored in this browser and applied before the first paint.', 'portfolio-theme' ); ?></p>
		<p class="meta">
			<?php esc_html_e( 'Current:', 'portfolio-theme' ); ?>
			<output data-theme-current><?php esc_html_e( 'requires JavaScript', 'portfolio-theme' ); ?></output>
		</p>
	</section>

	<section aria-labelledby="ds-icons">
		<h2 id="ds-icons"><?php esc_html_e( 'Icons', 'portfolio-theme' ); ?></h2>
		<p><?php esc_html_e( 'Lucide icons on a 24-unit grid, with a thinner stroke. They take the text colour and are decorative: the text next to them carries the meaning. Editors pick them in a reusable icon field.', 'portfolio-theme' ); ?></p>

		<ul class="ds-icons" role="list">
			<?php foreach ( portfolio_theme_icon_names() as $portfolio_icon ) : ?>
				<li>
					<?php portfolio_theme_icon( $portfolio_icon ); ?>
					<span class="meta"><?php echo esc_html( $portfolio_icon ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php $portfolio_card_icon = function_exists( 'get_field' ) ? get_field( 'demo_card_icon' ) : ''; ?>
		<div class="ds-card">
			<?php portfolio_theme_icon( $portfolio_card_icon ? $portfolio_card_icon : 'blocks' ); ?>
			<p class="ds-card-title"><?php esc_html_e( 'Demo card', 'portfolio-theme' ); ?></p>
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

	<section aria-labelledby="ds-details">
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

		<table class="ds-table">
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

	<section aria-labelledby="ds-layout">
		<h2 id="ds-layout"><?php esc_html_e( 'Layout', 'portfolio-theme' ); ?></h2>
		<table class="ds-table">
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
