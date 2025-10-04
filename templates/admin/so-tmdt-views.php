<?php
use ASS\SoTMDT\PostType as SoTMDTPostType;
use ASS\Serial\PostType as SerialPostType;
use ASS\Helper;
?>

<div class="wrap">
	<?php

	echo '<div class="wrap"><h1>Thông tin kho Số TMDT</h1>';
	echo '<form method="GET" class="filter-form">';
	echo '</form>';

	// Hiển thị bảng
	echo '<div class="wrap"><table class="table-serial widefat fixed">';
	echo '<thead><tr>
			<th>STT</th>
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
			<th>Gán Serial Sim</th>
			<th>Hành động</th>
			</tr></thead>';
	echo '<tbody>';

	$data_nha_mang = Helper::nha_mang();
	$data_so_tmdt = SoTMDTPostType::get_data();
	$data_serial = SerialPostType::serial_get_data();
	$serials = array_column($data_serial, 'serial_sim');

	foreach ( $data_so_tmdt as $key => $nv ) {
		?>
		<tr>
			<td><?php echo esc_html( $key + 1 ) ?></td>
			<td data-field="sdt"><?php echo esc_html( $nv['sdt'] ) ?></td>
			<td data-field="sdt_chamdinhdang" class="editable"><?php echo esc_html( $nv['sdt_chamdinhdang'] ) ?></td>
			<td data-field="dinh_dang_sim" class="editable"><?php echo esc_html( $nv['dinh_dang_sim'] ) ?></td>
			<td data-field="nha_mang" class="editable" data-type="select" data-options='<?= esc_attr( json_encode( $data_nha_mang, JSON_UNESCAPED_UNICODE ) ) ?>'><?= esc_html( $nv['nha_mang'] ) ?></td>
			<td data-field="loai_sim" class="editable" data-type="select" data-options='["Trả trước","Trả sau"]'><?= esc_html( $nv['loai_sim'] ) ?></td>
			<td data-field="cam_ket" class="editable"><?php echo esc_html( $nv['cam_ket'] ) ?></td>
			<td data-field="goi_cuoc" class="editable"><?php echo esc_html( $nv['goi_cuoc'] ) ?></td>
			<td data-field="kenh_ban" class="editable"><?php echo esc_html( $nv['kenh_ban'] ) ?></td>
			<td data-field="tinh_trang_ban" class="editable"><?php echo esc_html( $nv['tinh_trang_ban'] ) ?></td>
			<td data-field="ghi_chu" class="editable"><?php echo esc_html( $nv['ghi_chu'] ) ?></td>
			<td data-field="serial_sim"
				class="editable"
				data-type="select"
				data-options='<?= esc_attr( json_encode( $serials, JSON_UNESCAPED_UNICODE ) ) ?>'
			>
				<?= esc_html( $nv['serial_sim'] ) ?>
			</td>
			<td>
				<button class="btn-edit" data-id="<?= $nv['so_tmdt_id'] ?>">✏️</button>
				<button class="btn-save" data-id="<?= $nv['so_tmdt_id'] ?>" style="display:none;">💾</button>
			</td>
		</tr>
		<?php
	}

	echo '</tbody></table></div>';
	?>
</div>
