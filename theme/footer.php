<?php
/**
 * Site footer: copyright and the footer links menu. Closes the site block
 * opened in header.php.
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
	?>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
