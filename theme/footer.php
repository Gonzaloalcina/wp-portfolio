<?php
/**
 * Site footer in two columns from 48rem: copyright with the LinkedIn icon
 * under it, and the footer links with the legal links and "Cookie settings"
 * under them, both on the end edge. Below 48rem: copyright and the icon on
 * one row, then each menu on its own row.
 * Closes the site block opened in header.php.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<footer class="site__footer">
	<p class="site__footer-text">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	<?php
	portfolio_theme_component(
		'link-row-a',
		array(
			'links' => portfolio_theme_menu_links( 'footer' ),
			'class' => 'site__footer-links',
		)
	);
	portfolio_theme_component(
		'link-row-a',
		array(
			'links' => portfolio_theme_linkedin_link(),
			'class' => 'site__footer-social',
		)
	);
	portfolio_theme_component(
		'link-row-a',
		array(
			'links' => array_merge( portfolio_theme_menu_links( 'legal' ), portfolio_theme_consent_link() ),
			'class' => 'site__footer-links site__footer-legal',
		)
	);
	?>
	<span id="new-tab-note" hidden><?php esc_html_e( 'Opens in a new tab', 'portfolio-theme' ); ?></span>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
