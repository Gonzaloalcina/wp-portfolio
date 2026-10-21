<?php
/**
 * Site footer: copyright, the footer links menu and "Cookie settings".
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
			'links' => array_merge( portfolio_theme_menu_links( 'footer' ), portfolio_theme_consent_link() ),
			'class' => 'site__footer-links',
		)
	);
	?>
	<span id="new-tab-note" hidden><?php esc_html_e( 'Opens in a new tab', 'portfolio-theme' ); ?></span>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
