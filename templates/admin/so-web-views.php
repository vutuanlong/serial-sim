<?php
use ASS\SoWeb\PostType;
?>

<div class="wrap">
	<?php

	$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : '';
	$end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : '';
	$orderby    = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'chi_phi_don';
	$order      = isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : 'DESC';

	// Form lọc ngày
	echo '<div class="wrap"><h1>Thông tin kho Số bán web</h1>';
	echo '<form method="GET" class="filter-form">';
	echo '</form>';

	// Hiển thị bảng
	echo '<div class="wrap"><table class="widefat fixed">';
	echo '<thead><tr>
			<th>STT</th>
			<th>SDT - Định dạng thường</th>
			<th>SDT - Chấm định dạng</th>
			<th>ID kho</th>
			<th>Cọc sim</th>
			<th>Giá bán lẻ</th>
			<th>Giá đại lý</th>
			<th>Loại sim</th>
			<th>Cam kết</th>
			<th>Kênh bán hàng</th>
			<th>Ngày bán</th>
			<th>Tình trạng bán hàng</th>
			<th>Ghi chú</th>
			</tr></thead>';
	echo '<tbody>';

	$data_so_tmdt = PostType::get_data();

	foreach ( $data_so_tmdt as $key => $nv ) {
		echo '<tr>
				<td>' . esc_html( $key + 1 ) . '</td>
				<td>' . esc_html( $nv['sdt'] ) . '</td>
				<td>' . esc_html( $nv['sdt_chamdinhdang'] ) . '</td>
				<td>' . esc_html( $nv['id_kho'] ) . '</td>
				<td>' . esc_html( $nv['coc_sim'] ) . '</td>
				<td>' . esc_html( $nv['gia_ban_le'] ) . '</td>
				<td>' . esc_html( $nv['gia_dai_ly'] ) . '</td>
				<td>' . esc_html( $nv['loai_sim'] ) . '</td>
				<td>' . esc_html( $nv['cam_ket'] ) . '</td>
				<td>' . esc_html( $nv['kenh_ban'] ) . '</td>
				<td>' . esc_html( $nv['ngay_ban'] ) . '</td>
				<td>' . esc_html( $nv['tinh_trang_ban'] ) . '</td>
				<td>' . esc_html( $nv['ghi_chu'] ) . '</td>
				</tr>';

	}

	echo '</tbody></table></div>';
	?>
</div>
