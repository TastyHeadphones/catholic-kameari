<?php
/**
 * Front page — liturgical calendar.
 *
 * Rows with a sort date in the past are dropped, so an out-of-date calendar
 * shrinks rather than showing stale feasts.
 *
 * @package Kameari_Church
 */

$rows  = kameari_get_rows( 'kameari_event' );
$today = wp_date( 'Y-m-d' );

$rows = array_values( array_filter( $rows, function ( $row ) use ( $today ) {
	$date = kameari_meta( $row->ID, 'date' );
	return ! $date || $date >= $today;
} ) );

if ( ! $rows ) {
	return;
}

$rows = array_slice( $rows, 0, 8 );
?>
<section class="section paper">
	<div class="container-wide">
		<?php
		kameari_section_head(
			'04',
			__( 'Liturgical calendar · 典礼暦', 'kameari-church' ),
			__( "近い主日と\n祝日のミサ", 'kameari-church' )
		);
		?>

		<div class="liturgy-grid">
			<div class="liturgy-art">
				<?php kameari_art( 'chalice', array( 'ratio' => '1/1' ) ); ?>
			</div>

			<div class="liturgy-list">
				<?php foreach ( $rows as $row ) : ?>
					<div class="liturgy-row">
						<div class="liturgy-date">
							<span class="d"><?php echo esc_html( kameari_meta( $row->ID, 'date_label' ) ); ?></span>
							<?php $weekday = kameari_meta( $row->ID, 'weekday' ); ?>
							<?php if ( $weekday ) : ?>
								<span class="w">（<?php echo esc_html( $weekday ); ?>）</span>
							<?php endif; ?>
						</div>
						<div class="liturgy-name"><?php echo esc_html( get_the_title( $row ) ); ?></div>
						<div class="liturgy-time tabnum"><?php echo esc_html( kameari_meta( $row->ID, 'time' ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
