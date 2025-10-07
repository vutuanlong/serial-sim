<?php
use ASS\SoWeb\PostType;
use ASS\Helper;
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
	echo '<div class="wrap"><table class="table-serial widefat fixed">';
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
			<th>Thao tác</th>
			</tr></thead>';
	echo '<tbody>';

	$data_so_tmdt = PostType::get_data();
	$data_nha_mang = Helper::nha_mang();

	foreach ( $data_so_tmdt as $key => $nv ) {
		?>
		<tr>
			<td><?php echo esc_html( $key + 1 ) ?></td>
			<td data-field="sdt"><?php echo esc_html( $nv['sdt'] ) ?></td>
			<td data-field="sdt_chamdinhdang" class="editable"><?php echo esc_html( $nv['sdt_chamdinhdang'] ) ?></td>
			<td data-field="id_kho" class="editable" data-type="select" data-options='<?= esc_attr( json_encode( $data_nha_mang, JSON_UNESCAPED_UNICODE ) ) ?>'><?php echo esc_html( $nv['id_kho'] ) ?></td>
			<td data-field="coc_sim" class="editable"><?php echo esc_html( $nv['coc_sim'] ) ?></td>
			<td data-field="gia_ban_le" class="editable"><?php echo esc_html( $nv['gia_ban_le'] ) ?></td>
			<td data-field="gia_dai_ly" class="editable"><?php echo esc_html( $nv['gia_dai_ly'] ) ?></td>
			<td data-field="loai_sim" class="editable" data-type="select" data-options='["Trả trước","Trả sau"]'><?= esc_html( $nv['loai_sim'] ) ?></td>
			<td data-field="cam_ket" class="editable"><?php echo esc_html( $nv['cam_ket'] ) ?></td>
			<td data-field="kenh_ban" class="editable"><?php echo esc_html( $nv['kenh_ban'] ) ?></td>
			<td data-field="ngay_ban" class="editable"><?php echo esc_html( $nv['ngay_ban'] ) ?></td>
			<td data-field="tinh_trang_ban" class="editable"><?php echo esc_html( $nv['tinh_trang_ban'] ) ?></td>
			<td data-field="ghi_chu" class="editable"><?php echo esc_html( $nv['ghi_chu'] ) ?></td>
			<td>
				<button class="btn-edit" data-id="<?= $nv['so_web_id'] ?>">✏️</button>
				<button class="btn-save-so-web" data-id="<?= $nv['so_web_id'] ?>" style="display:none;">💾</button>
			</td>
		</tr>
		<?php

	}

	echo '</tbody></table></div>';
	?>
</div>
