<?php
/**
 * Document head, masthead and mobile navigation.
 *
 * @package Kameari_Church
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'shell' ); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'kameari-church' ); ?></a>

<header class="header">
	<div class="container-wide header-inner">
		<?php kameari_brand(); ?>

		<nav class="nav" aria-label="<?php esc_attr_e( 'Primary', 'kameari-church' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav-list',
				'depth'          => 2,
				'fallback_cb'    => 'kameari_nav_fallback',
			) );

			$cta_url   = kameari_mod( 'kameari_visit_url' );
			$cta_label = kameari_mod( 'kameari_visit_label' );
			if ( $cta_url && $cta_label ) :
				?>
				<a class="nav-cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
			<?php endif; ?>
		</nav>

		<button class="menu-btn" type="button"
			aria-label="<?php esc_attr_e( 'Open menu', 'kameari-church' ); ?>"
			aria-expanded="false" aria-controls="kameari-mobile-nav"
			data-kameari-menu-open>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false">
				<line x1="3" y1="8" x2="21" y2="8" />
				<line x1="3" y1="16" x2="21" y2="16" />
			</svg>
		</button>
	</div>
</header>

<div class="mobile-nav" id="kameari-mobile-nav" aria-hidden="true">
	<div class="mobile-nav-head">
		<?php kameari_brand( array( 'subtitle' => false ) ); ?>
		<button class="menu-btn" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'kameari-church' ); ?>" data-kameari-menu-close>
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false">
				<line x1="5" y1="5" x2="19" y2="19" />
				<line x1="19" y1="5" x2="5" y2="19" />
			</svg>
		</button>
	</div>

	<nav aria-label="<?php esc_attr_e( 'Mobile', 'kameari-church' ); ?>">
		<?php
		wp_nav_menu( array(
			'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
			'container'      => false,
			'menu_class'     => 'mobile-nav-list',
			'depth'          => 2,
			'fallback_cb'    => 'kameari_nav_fallback',
		) );
		?>
	</nav>

	<?php
	$foot = array_filter( array(
		kameari_mod( 'kameari_postal' ),
		kameari_mod( 'kameari_address' ),
		kameari_mod( 'kameari_phone' ) ? 'TEL ' . kameari_mod( 'kameari_phone' ) : '',
	) );

	if ( $foot ) :
		?>
		<div class="mobile-nav-foot"><?php echo esc_html( implode( ' · ', $foot ) ); ?></div>
	<?php endif; ?>
</div>

<main class="site-main" id="content">
