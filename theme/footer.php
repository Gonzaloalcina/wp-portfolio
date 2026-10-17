<?php
/**
 * Site footer. Closes the site block opened in header.php.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<footer class="site__footer">
	<p class="site__footer-text">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
