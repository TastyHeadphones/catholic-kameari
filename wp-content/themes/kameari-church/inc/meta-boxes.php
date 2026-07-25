<?php
/**
 * Meta boxes for the parish post types.
 *
 * Plain core meta boxes on purpose — no ACF or other plugin dependency, so the
 * theme keeps working if a plugin is deactivated.
 *
 * @package Kameari_Church
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field definitions, keyed by post type.
 *
 * @return array
 */
function kameari_meta_fields() {
	return array(
		'kameari_mass' => array(
			'title'  => __( 'Mass details', 'kameari-church' ),
			'fields' => array(
				'_kameari_day_jp'   => array( 'label' => __( 'Day (Japanese)', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 日曜日 / 月・水・金', 'kameari-church' ) ),
				'_kameari_day_en'   => array( 'label' => __( 'Day (Latin letters)', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. SUN / MON · WED · FRI', 'kameari-church' ) ),
				'_kameari_time'     => array( 'label' => __( 'Time', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 10:00', 'kameari-church' ) ),
				'_kameari_note'     => array( 'label' => __( 'Note', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'Small line under the name.', 'kameari-church' ) ),
				'_kameari_featured' => array( 'label' => __( 'Highlight this row', 'kameari-church' ), 'type' => 'checkbox', 'hint' => __( 'Use for the principal Sunday Mass.', 'kameari-church' ) ),
			),
		),
		'kameari_event' => array(
			'title'  => __( 'Liturgical date', 'kameari-church' ),
			'fields' => array(
				'_kameari_date_label' => array( 'label' => __( 'Date label', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 5月10日', 'kameari-church' ) ),
				'_kameari_weekday'    => array( 'label' => __( 'Weekday', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 日', 'kameari-church' ) ),
				'_kameari_time'       => array( 'label' => __( 'Time', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 10:00', 'kameari-church' ) ),
				'_kameari_date'       => array( 'label' => __( 'Sort date', 'kameari-church' ), 'type' => 'date', 'hint' => __( 'Optional. Past dates are hidden from the front page.', 'kameari-church' ) ),
			),
		),
		'kameari_activity' => array(
			'title'  => __( 'Activity details', 'kameari-church' ),
			'fields' => array(
				'_kameari_link' => array( 'label' => __( 'Link', 'kameari-church' ), 'type' => 'url', 'hint' => __( 'Page this tile opens. Leave blank for a non-clickable tile.', 'kameari-church' ) ),
			),
		),
		'kameari_clergy' => array(
			'title'  => __( 'Priest details', 'kameari-church' ),
			'fields' => array(
				'_kameari_role'    => array( 'label' => __( 'Role', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 主任司祭', 'kameari-church' ) ),
				'_kameari_name_en' => array( 'label' => __( 'Name (Latin letters)', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. Fr. Irinel Dobos, OFM Conv.', 'kameari-church' ) ),
				'_kameari_years'   => array( 'label' => __( 'Years in post', 'kameari-church' ), 'type' => 'text', 'hint' => __( 'e.g. 在任 2020–', 'kameari-church' ) ),
				'_kameari_tone'    => array(
					'label'   => __( 'Portrait tone', 'kameari-church' ),
					'type'    => 'select',
					'hint'    => __( 'Used only when no featured image is set.', 'kameari-church' ),
					'options' => array(
						'ink'   => __( 'Ink (dark)', 'kameari-church' ),
						'paper' => __( 'Paper (light)', 'kameari-church' ),
					),
				),
			),
		),
	);
}

/**
 * Register the meta boxes.
 */
function kameari_add_meta_boxes() {
	foreach ( kameari_meta_fields() as $post_type => $box ) {
		add_meta_box(
			'kameari-details-' . $post_type,
			$box['title'],
			'kameari_render_meta_box',
			$post_type,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'kameari_add_meta_boxes' );

/**
 * Render a meta box.
 *
 * @param WP_Post $post Current post.
 */
function kameari_render_meta_box( $post ) {
	$all = kameari_meta_fields();
	if ( ! isset( $all[ $post->post_type ] ) ) {
		return;
	}

	wp_nonce_field( 'kameari_save_meta', 'kameari_meta_nonce' );

	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( $all[ $post->post_type ]['fields'] as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		$id    = 'kameari-field-' . sanitize_key( $key );

		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';

		switch ( $field['type'] ) {
			case 'checkbox':
				echo '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . ' />';
				break;

			case 'select':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
				foreach ( $field['options'] as $opt_value => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_value ) . '" ' . selected( $value, $opt_value, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;

			case 'date':
				echo '<input type="date" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
				break;

			case 'url':
				echo '<input type="url" class="large-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
				break;

			default:
				echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		if ( ! empty( $field['hint'] ) ) {
			echo '<p class="description">' . esc_html( $field['hint'] ) . '</p>';
		}

		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Persist meta box values.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 */
function kameari_save_meta( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$all = kameari_meta_fields();
	if ( ! isset( $all[ $post->post_type ] ) ) {
		return;
	}

	if ( ! isset( $_POST['kameari_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kameari_meta_nonce'] ) ), 'kameari_save_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( $all[ $post->post_type ]['fields'] as $key => $field ) {
		if ( 'checkbox' === $field['type'] ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, '1' );
			} else {
				delete_post_meta( $post_id, $key );
			}
			continue;
		}

		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}

		$raw = wp_unslash( $_POST[ $key ] );

		if ( 'url' === $field['type'] ) {
			$clean = esc_url_raw( $raw );
		} elseif ( 'select' === $field['type'] ) {
			$clean = array_key_exists( $raw, $field['options'] ) ? $raw : '';
		} else {
			$clean = sanitize_text_field( $raw );
		}

		if ( '' === $clean ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $clean );
		}
	}
}
add_action( 'save_post', 'kameari_save_meta', 10, 2 );

/**
 * Show the key fields as admin list columns so the schedule is readable at a glance.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function kameari_mass_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['kameari_day']  = __( 'Day', 'kameari-church' );
			$new['kameari_time'] = __( 'Time', 'kameari-church' );
		}
	}
	return $new;
}
add_filter( 'manage_kameari_mass_posts_columns', 'kameari_mass_columns' );
add_filter( 'manage_kameari_event_posts_columns', 'kameari_mass_columns' );

/**
 * Render the custom admin columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function kameari_mass_column_content( $column, $post_id ) {
	if ( 'kameari_day' === $column ) {
		$day = get_post_meta( $post_id, '_kameari_day_jp', true );
		if ( ! $day ) {
			$day = get_post_meta( $post_id, '_kameari_date_label', true );
		}
		echo esc_html( $day );
	}

	if ( 'kameari_time' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_kameari_time', true ) );
	}
}
add_action( 'manage_kameari_mass_posts_custom_column', 'kameari_mass_column_content', 10, 2 );
add_action( 'manage_kameari_event_posts_custom_column', 'kameari_mass_column_content', 10, 2 );
