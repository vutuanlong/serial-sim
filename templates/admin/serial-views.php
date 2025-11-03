<?php
use ASS\Serial\PostType;
?>

<div class="wrap">
	<h1>Thông tin Serial Sim + kho số TMDT</h1>
	<form method="GET" class="filter-form">
		<input type="hidden" name="post_type" value="serial">
		<input type="hidden" name="page" value="thong-tin">
		<a href="<?= esc_url( add_query_arg( array_merge( $_GET, [ 'export' => 'excel' ] ) ) ) ?>" class="button">Xuất Excel</a>
	</form>

	<div class="wrap">
		<table class="table-serial widefat fixed">
			<thead>
				<tr>
					<th>STT</th>
					<th>Ngày nhập Serial</th>
					<th>Serial Sim</th>
					<th>SDT</th>
					<!-- <th>SDT - Chấm định dạng</th>
					<th>Định dạng sim</th>
					<th>Nhà mạng</th>
					<th>Loại sim</th>
					<th>Cam kết</th>
					<th>Gói cước</th>
					<th>Kênh bán hàng</th>
					<th>Tình trạng bán hàng</th>
					<th>Ghi chú</th> -->
					<!-- <th>Hành động</th> -->
				</tr>
			</thead>
			<tbody>

				<?php

				$data_serial = PostType::serial_get_data();

				foreach ( $data_serial as $key => $nv ) {
					?>
					<tr>
						<td><?= esc_html( $key + 1 ) ?></td>
						<td data-field="ngay_nhap"><?= esc_html( $nv['ngay_nhap'] ) ?></td>
						<td data-field="serial_sim"><?= esc_html( $nv['serial_sim'] ) ?></td>
						<td data-field="sdt"><?= esc_html( $nv['sdt'] ) ?></td>
						<!-- <td data-field="sdt_chamdinhdang" class="editable"><?= esc_html( $nv['sdt_chamdinhdang'] ) ?></td>
						<td data-field="dinh_dang_sim" class="editable"><?= esc_html( $nv['dinh_dang_sim'] ) ?></td>
						<td data-field="nha_mang" class="editable"><?= esc_html( $nv['nha_mang'] ) ?></td>
						<td data-field="loai_sim" class="editable"><?= esc_html( $nv['loai_sim'] ) ?></td>
						<td data-field="cam_ket" class="editable"><?= esc_html( $nv['cam_ket'] ) ?></td>
						<td data-field="goi_cuoc" class="editable"><?= esc_html( $nv['goi_cuoc'] ) ?></td>
						<td data-field="kenh_ban" class="editable"><?= esc_html( $nv['kenh_ban'] ) ?></td>
						<td data-field="tinh_trang_ban" class="editable"><?= esc_html( $nv['tinh_trang_ban'] ) ?></td>
						<td data-field="ghi_chu" class="editable"><?= esc_html( $nv['ghi_chu'] ) ?></td> -->
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>
