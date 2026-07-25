<?php
/**
 * Front page — hero.
 *
 * @package Kameari_Church
 */

$image = kameari_mod( 'kameari_hero_image' );
$b1    = kameari_mod( 'kameari_hero_btn1_label' );
$b2    = kameari_mod( 'kameari_hero_btn2_label' );
?>
<section class="hero">
	<?php if ( $image ) : ?>
		<div class="hero-bg">
			<img src="<?php echo esc_url( $image ); ?>" alt="" fetchpriority="high" decoding="async" />
		</div>
	<?php endif; ?>
	<div class="hero-grad"></div>

	<div class="container-wide hero-inner">
		<div class="hero-grid">
			<div class="hero-text">
				<?php kameari_label( kameari_mod( 'kameari_hero_kicker' ) ); ?>
				<h1><?php echo kameari_lines( kameari_mod( 'kameari_hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in kameari_lines(). ?></h1>
				<?php $lede = kameari_mod( 'kameari_hero_lede' ); ?>
				<?php if ( $lede ) : ?>
					<p class="lede"><?php echo esc_html( $lede ); ?></p>
				<?php endif; ?>

				<?php if ( $b1 || $b2 ) : ?>
					<div class="hero-actions">
						<?php if ( $b1 ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( kameari_url( kameari_mod( 'kameari_hero_btn1_url' ) ) ); ?>">
								<?php echo esc_html( $b1 ); ?> <span class="arr" aria-hidden="true">→</span>
							</a>
						<?php endif; ?>
						<?php if ( $b2 ) : ?>
							<a class="btn btn-ghost" href="<?php echo esc_url( kameari_url( kameari_mod( 'kameari_hero_btn2_url' ) ) ); ?>">
								<?php echo esc_html( $b2 ); ?> <span class="arr" aria-hidden="true">→</span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php $next_date = kameari_mod( 'kameari_hero_next_date' ); ?>
			<?php if ( $next_date ) : ?>
				<div class="hero-meta">
					<div><?php echo esc_html( kameari_mod( 'kameari_hero_next_label' ) ); ?></div>
					<div class="next">
						<?php echo esc_html( $next_date ); ?>
						<span class="time tabnum"><?php echo esc_html( kameari_mod( 'kameari_hero_next_time' ) ); ?></span>
					</div>
					<?php $note = kameari_mod( 'kameari_hero_note' ); ?>
					<?php if ( $note ) : ?>
						<div class="note"><?php echo esc_html( $note ); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
