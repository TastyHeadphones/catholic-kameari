<?php
/**
 * Front page — parish activities grid.
 *
 * @package Kameari_Church
 */

$all   = kameari_get_rows( 'kameari_activity' );
$total = count( $all );

if ( ! $total ) {
	return;
}

$rows = array_slice( $all, 0, 6 );
?>
<section class="section">
	<div class="container-wide">
		<?php
		kameari_section_head(
			'05',
			__( 'Parish life · 教会活動', 'kameari-church' ),
			__( "共同体としての\n歩み", 'kameari-church' )
		);
		?>

		<div class="life-grid">
			<?php foreach ( $rows as $index => $row ) : ?>
				<?php
				$link = kameari_meta( $row->ID, 'link' );
				$tag  = $link ? 'a' : 'div';
				?>
				<<?php echo esc_html( $tag ); ?> class="life-tile"<?php echo $link ? ' href="' . esc_url( $link ) . '"' : ''; ?>>
					<div class="num"><?php echo esc_html( sprintf( '%02d / %02d', $index + 1, $total ) ); ?></div>
					<h4><?php echo esc_html( get_the_title( $row ) ); ?></h4>
					<p><?php echo esc_html( kameari_get_excerpt( 64, $row->ID ) ); ?></p>
					<?php if ( $link ) : ?>
						<span class="arr-link"><?php esc_html_e( '詳しく見る', 'kameari-church' ); ?> <span aria-hidden="true">→</span></span>
					<?php endif; ?>
				</<?php echo esc_html( $tag ); ?>>
			<?php endforeach; ?>
		</div>
	</div>
</section>
