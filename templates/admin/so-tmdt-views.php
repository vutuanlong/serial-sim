<?php
use ASS\SoTMDT\PostType as SoTMDTPostType;
use ASS\Serial\PostType as SerialPostType;
use ASS\Helper;

$data_nha_mang = Helper::nha_mang();
$data_dinh_dang_sim = Helper::dinh_dang_sim();
$data_loai_sim = Helper::loai_sim();
$data_tinh_trang = Helper::tinh_trang_ban_hang();

?>

<div class="wrap">
	<h1>Thông tin kho Số TMDT</h1>
	<div style="display: flex; gap: 15px; margin-top: 10px; justify-content: space-between;">
		<form method="GET" class="filter-form">
			<input type="hidden" name="post_type" value="so-tmdt">
			<input type="hidden" name="page" value="thong-tin-so-tmdt">
			<a href="<?= esc_url( add_query_arg( array_merge( $_GET, [ 'export' => 'excel' ] ) ) ) ?>" class="button">Xuất Excel</a>
		</form>

		<form method="get" style="display:flex; gap:15px;">
			<input type="hidden" name="post_type" value="so-tmdt">
			<input type="hidden" name="page" value="thong-tin-so-tmdt">
			<input type="text"
				name="search_sdt"
				placeholder="Tìm theo số không chấm"
				value="<?php echo esc_attr( $_GET['search_sdt'] ?? '' ); ?>"
				style="padding:6px 10px; width:220px;">

			<input type="text"
				name="search_serial"
				placeholder="Tìm theo serial sim"
				value="<?php echo esc_attr( $_GET['search_serial'] ?? '' ); ?>"
				style="padding:6px 10px; width:220px;">

			<button type="submit" class="button">Lọc</button>
		</form>
	</div>

	<div class="wrap" style="margin-right: 0;">
		<table class="table-serial widefat fixed">
			<thead>
				<tr>
					<th>STT</th>
					<th>SDT - Định dạng thường</th>
					<th>SDT - Chấm định dạng</th>
					<th>
						<select id="filter-dinh_dang_sim" class="filter-select" name="dinh_dang_sim">
							<option value="">Định dạng sim</option>
							<?php foreach ( $data_dinh_dang_sim as $dinh_dang_sim ) : ?>
								<option value="<?= esc_attr( $dinh_dang_sim ) ?>" <?php selected( $_GET['nha_mang'] ?? '', $dinh_dang_sim ); ?>>
									<?= esc_html( $dinh_dang_sim ) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</th>
					<th>
						<select id="filter-nha-mang" class="filter-select" name="nha_mang">
							<option value="">Nhà mạng</option>
							<?php foreach ( $data_nha_mang as $nha_mang ) : ?>
								<option value="<?= esc_attr( $nha_mang ) ?>" <?php selected( $_GET['nha_mang'] ?? '', $nha_mang ); ?>>
									<?= esc_html( $nha_mang ) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</th>
					<th>
						<select id="filter-loai_sim" class="filter-select" name="loai_sim">
							<option value="">Loại sim</option>
							<?php foreach ( $data_loai_sim as $loai_sim ) : ?>
								<option value="<?= esc_attr( $loai_sim ) ?>" <?php selected( $_GET['loai_sim'] ?? '', $loai_sim ); ?>>
									<?= esc_html( $loai_sim ) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</th>
					<th>Cọc sim</th>
					<th>Cam kết</th>
					<th>Gói cước</th>
					<th>Kênh bán hàng</th>
					<th>
						<select id="filter-tinh_trang_ban" class="filter-select" name="tinh_trang_ban">
							<option value="">Tình trạng bán hàng</option>
							<?php foreach ( $data_tinh_trang as $tinh_trang_ban ) : ?>
								<option value="<?= esc_attr( $tinh_trang_ban ) ?>" <?php selected( $_GET['tinh_trang_ban'] ?? '', $tinh_trang_ban ); ?>>
									<?= esc_html( $tinh_trang_ban ) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</th>
					<th>Mã đơn hàng</th>
					<th>Ghi chú</th>
					<th>Gán Serial Sim</th>
					<th>Thao tác</th>
				</tr>
			</thead>
			<tbody>
				<?php

				$data_so_tmdt = SoTMDTPostType::get_data();
				$data_serial = SerialPostType::serial_get_data();
				$serials = array_column( $data_serial, 'serial_sim' );

				foreach ( $data_so_tmdt as $key => $nv ) {
					?>
					<tr>
						<td><?php echo esc_html( $key + 1 ) ?></td>
						<td data-field="sdt"><?php echo esc_html( $nv['sdt'] ) ?></td>
						<td data-field="sdt_chamdinhdang" class="editable"><?php echo esc_html( $nv['sdt_chamdinhdang'] ) ?></td>
						<td data-field="dinh_dang_sim"
							class="editable"
							data-type="select"
							data-options='<?= esc_attr( json_encode( $data_dinh_dang_sim, JSON_UNESCAPED_UNICODE ) ) ?>'
						>
							<?php echo esc_html( $nv['dinh_dang_sim'] ) ?>
						</td>
						<td data-field="nha_mang" class="editable" data-type="select" data-options='<?= esc_attr( json_encode( $data_nha_mang, JSON_UNESCAPED_UNICODE ) ) ?>'><?= esc_html( $nv['nha_mang'] ) ?></td>
						<td data-field="loai_sim" class="editable" data-type="select" data-options='<?= esc_attr( json_encode( $data_loai_sim, JSON_UNESCAPED_UNICODE ) ) ?>'><?= esc_html( $nv['loai_sim'] ) ?></td>
						<td data-field="coc_sim" class="editable"><?= esc_html( $nv['coc_sim'] ) ?></td>
						<td data-field="cam_ket" class="editable"><?php echo esc_html( $nv['cam_ket'] ) ?></td>
						<td data-field="goi_cuoc" class="editable"><?php echo esc_html( $nv['goi_cuoc'] ) ?></td>
						<td data-field="kenh_ban" class="editable"><?php echo esc_html( $nv['kenh_ban'] ) ?></td>
						<td data-field="tinh_trang_ban"
							class="editable"
							data-type="select"
							data-options='<?= esc_attr( json_encode( $data_tinh_trang, JSON_UNESCAPED_UNICODE ) ) ?>'
						>
							<?php echo esc_html( $nv['tinh_trang_ban'] ) ?>
						</td>
						<td data-field="ma_don_hang" class="editable"><?php echo esc_html( $nv['ma_don_hang'] ) ?></td>
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
				?>
			</tbody>
		</table>
	</div>
</div>
