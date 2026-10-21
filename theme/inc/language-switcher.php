<?php
/**
 * Language switcher for the header: the languages Polylang offers, as quiet
 * text links next to the theme toggle ("EN / ES"). Nothing renders without
 * Polylang or with a single language. Each link goes to the translation of the
 * current page, or to the home of that language when there is none.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the language switcher.
 */
function portfolio_theme_language_switcher() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}

	$languages = pll_the_languages( array( 'raw' => 1 ) );
	if ( ! is_array( $languages ) || count( $languages ) < 2 ) {
		return;
	}
	?>
	<nav class="lang-switch" aria-label="<?php esc_attr_e( 'Language', 'portfolio-theme' ); ?>">
		<ul class="lang-switch__list" role="list">
			<?php foreach ( $languages as $portfolio_language ) : ?>
				<li class="lang-switch__item">
					<a
						class="lang-switch__link"
						href="<?php echo esc_url( $portfolio_language['url'] ); ?>"
						lang="<?php echo esc_attr( $portfolio_language['slug'] ); ?>"
						hreflang="<?php echo esc_attr( $portfolio_language['slug'] ); ?>"
						<?php echo ! empty( $portfolio_language['current_lang'] ) ? 'aria-current="true"' : ''; ?>
					>
						<span aria-hidden="true"><?php echo esc_html( strtoupper( $portfolio_language['slug'] ) ); ?></span>
						<span class="screen-reader-text"><?php echo esc_html( $portfolio_language['name'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}
