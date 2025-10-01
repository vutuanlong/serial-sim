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
	echo '<div class="wrap"><table class="table-serial widefat fixed">';
	echo '<thead><tr>
			<th>STT</th>
			<th>Ngày nhập Serial</th>
			<th>Serial Sim</th>
			<th>SDT - Định dạng thường</th>
			<th>SDT - Chấm định dạng</th>
			<th>Định dạng sim</th>
			<th>Nhà mạng</th>
			<th>Loại sim</th>
			<th>Cam kết</th>
			<th>Gói cước</th>
			<th>Kênh bán hàng</th>
			<th>Tình trạng bán hàng</th>
			<th>Ghi chú</th>
			<th>Hành động</th>
			</tr></thead>';
	echo '<tbody>';

	$data_serial = PostType::serial_get_data();

	foreach ( $data_serial as $key => $nv ) {
		echo '<tr>
				<td>' . esc_html( $key + 1 ) . '</td>
				<td data-field="ngay_nhap">' . esc_html( $nv['ngay_nhap'] ) . '</td>
				<td data-field="serial_sim">' . esc_html( $nv['serial_sim'] ) . '</td>
				<td data-field="sdt">' . esc_html( $nv['sdt'] ) . '</td>
				<td data-field="sdt_chamdinhdang" class="editable">' . esc_html( $nv['sdt_chamdinhdang'] ) . '</td>
				<td data-field="dinh_dang_sim">' . esc_html( $nv['dinh_dang_sim'] ) . '</td>
				<td data-field="nha_mang">' . esc_html( $nv['nha_mang'] ) . '</td>
				<td data-field="loai_sim">' . esc_html( $nv['loai_sim'] ) . '</td>
				<td data-field="cam_ket">' . esc_html( $nv['cam_ket'] ) . '</td>
				<td data-field="goi_cuoc">' . esc_html( $nv['goi_cuoc'] ) . '</td>
				<td data-field="kenh_ban">' . esc_html( $nv['kenh_ban'] ) . '</td>
				<td data-field="tinh_trang_ban">' . esc_html( $nv['tinh_trang_ban'] ) . '</td>
				<td data-field="ghi_chu">' . esc_html( $nv['ghi_chu'] ) . '</td>
				<td>
					<button class="btn-edit" data-id="' . $nv['serial_id'] . '">✏️</button>
					<button class="btn-save" data-id="' . $nv['serial_id'] . '" style="display:none;">💾</button>
				</td>
				</tr>';

	}

	echo '</tbody></table></div>';
	?>
</div>
