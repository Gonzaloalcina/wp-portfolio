<?php
/**
 * Site footer.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<footer class="site-footer">
	<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
</footer>
<?php wp_footer(); ?>
</body>
</html>
