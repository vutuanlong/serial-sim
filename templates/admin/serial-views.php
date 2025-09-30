<?php
use ASS\Serial\PostType;
?>

<div class="wrap">
	<?php

	$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
	$end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
	$orderby    = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'chi_phi_don';
	$order      = isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : 'DESC';

	// Form lọc ngày
	echo '<div class="wrap"><h1>Thông tin Serial Sim</h1>';
	echo '<form method="GET" class="filter-form">';
	echo '</form>';

	// Hiển thị bảng
	echo '<div class="wrap"><table class="widefat fixed">';
	echo '<thead><tr>
			<th>STT</th>
			<th>Ngày nhập Serial</th>
			<th>Serial Sim</th>
			</tr></thead>';
	echo '<tbody>';

	$data_serial = PostType::serial_get_data();

	foreach ( $data_serial as $key => $nv ) {
		echo '<tr>
				<td>' . esc_html( $key + 1 ) . '</td>
				<td>' . esc_html( $nv['ngay_nhap'] ) . '</td>
				<td>' . esc_html( $nv['serial_sim'] ) . '</td>
				</tr>';

	}

	echo '</tbody></table></div>';
	?>
</div>
