<?php

namespace ASS\SoTMDT;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\IOFactory;

use ASS\Helper;

class Import {
	public function init() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'generate_xlsx' ] );
	}

	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=so-tmdt',
			'Nhập Số TMDT',
			'Nhập Số TMDT',
			'manage_options',
			'import-so-tmdt',
			[ $this, 'import_so_tmdt_excel' ]
		);

		add_submenu_page(
			'edit.php?post_type=so-tmdt',
			'Nhập Số TMDT + Serial Sim đã ghép',
			'Nhập Số TMDT + Serial Sim đã ghép',
			'manage_options',
			'import-so-tmdt-serial',
			[ $this, 'import_so_tmdt_serial_excel' ]
		);
	}

	public function import_so_tmdt_serial_excel() {
		echo '<div class="wrap"><h1>Nhập Số TMDT + Serial Sim đã ghép</h1>';

		// Form upload file
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'import_so_tmdt_serial_nonce', 'import_so_tmdt_serial_nonce_field' );

		$array_nha_mang = Helper::nha_mang();
		?>

		<p>
			<label>Chọn nhà mạng:</label><br>
			<select name="nha_mang" required>
				<?php foreach ( $array_nha_mang as $nha_mang ) : ?>
					<option value="<?= $nha_mang ?>"><?= $nha_mang ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label>Chọn loại sim:</label><br>
			<select name="loai_sim" required>
				<option value="Trả trước">Trả trước</option>
				<option value="Trả sau">Trả sau</option>
			</select>
		</p>

		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
		echo '<p>Tải file mẫu <a href="' . ASS_URL . 'import-serial-khoso.xlsx">tại đây</a></p>';
		submit_button( 'Upload' );
		echo '</form>';

		// Xử lý file upload
		if ( isset( $_FILES['import_file'] ) && ! empty( $_FILES['import_file']['tmp_name'] ) ) {
			if ( ! isset( $_POST['import_so_tmdt_serial_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_so_tmdt_serial_nonce_field'], 'import_so_tmdt_serial_nonce' ) ) {
				wp_die( 'Nonce verification failed' );
			}

			$file = $_FILES['import_file']['tmp_name'];
			$spreadsheet = IOFactory::load( $file );
			$sheet = $spreadsheet->getActiveSheet();

			$highestRow = $sheet->getHighestRow();

			$nha_mang  = sanitize_text_field( $_POST['nha_mang'] );
			$loai_sim  = sanitize_text_field( $_POST['loai_sim'] );

			for ( $row = 2; $row <= $highestRow; $row++ ) {
				$name_so_tmdt = trim( $sheet->getCell( 'D' . $row )->getValue() );
				$name_serial  = trim( $sheet->getCell( 'C' . $row )->getValue() );

				if ( empty( $name_so_tmdt ) ) {
					continue;
				}

				// check nếu Số TMDT đã tồn tại (title post type Số TMDT)
				$exists = get_page_by_title( $name_so_tmdt, OBJECT, 'so-tmdt' );

				if ( $exists ) {
					$errors[] = $name_so_tmdt;
					continue;
				}

				$post_id = wp_insert_post( [
					'post_title'  => $name_so_tmdt,
					'post_type'   => 'so-tmdt',
					'post_status' => 'publish',
				] );

				if ( $post_id ) {

					update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $sheet->getCell( 'E' . $row )->getValue() ) );
					update_post_meta( $post_id, 'dinh_dang_sim', trim( $sheet->getCell( 'F' . $row )->getValue() ) );
					update_post_meta( $post_id, 'nha_mang', $nha_mang );
					update_post_meta( $post_id, 'loai_sim', $loai_sim );
					update_post_meta( $post_id, 'coc_sim', trim( $sheet->getCell( 'I' . $row )->getValue() ) );
					update_post_meta( $post_id, 'cam_ket', trim( $sheet->getCell( 'J' . $row )->getValue() ) );
					update_post_meta( $post_id, 'goi_cuoc', trim( $sheet->getCell( 'K' . $row )->getValue() ) );
					update_post_meta( $post_id, 'kenh_ban', trim( $sheet->getCell( 'L' . $row )->getValue() ) );
					update_post_meta( $post_id, 'tinh_trang_ban', trim( $sheet->getCell( 'M' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ma_don_hang', trim( $sheet->getCell( 'N' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ghi_chu', trim( $sheet->getCell( 'O' . $row )->getValue() ) );
					update_post_meta( $post_id, 'serial_sim', $name_serial );
				}

				$post_id_serial = wp_insert_post( [
					'post_title'  => $name_serial,
					'post_type'   => 'serial',
					'post_status' => 'publish',
				] );
				if ( $post_id_serial ) {
					update_post_meta( $post_id_serial, 'sdt', sanitize_text_field( $name_so_tmdt ) );
					update_post_meta( $post_id_serial, 'ngay_nhap', trim( $sheet->getCell( 'B' . $row )->getValue() ) );
				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}

	public function import_so_tmdt_excel() {
		echo '<div class="wrap"><h1>Nhập Số TMDT</h1>';

		// Form upload file
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'import_so_tmdt_nonce', 'import_so_tmdt_nonce_field' );

		$array_nha_mang = Helper::nha_mang();
		?>

		<p>
			<label>Chọn nhà mạng:</label><br>
			<select name="nha_mang" required>
				<?php foreach ( $array_nha_mang as $nha_mang ) : ?>
					<option value="<?= $nha_mang ?>"><?= $nha_mang ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label>Chọn loại sim:</label><br>
			<select name="loai_sim" required>
				<option value="Trả trước">Trả trước</option>
				<option value="Trả sau">Trả sau</option>
			</select>
		</p>

		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
		echo '<p>Tải file mẫu <a href="' . ASS_URL . 'import-so-TMDT.xlsx">tại đây</a></p>';
		submit_button( 'Upload và Nhập Số TMDT' );
		echo '</form>';

		// Xử lý file upload
		if ( isset( $_FILES['import_file'] ) && ! empty( $_FILES['import_file']['tmp_name'] ) ) {
			if ( ! isset( $_POST['import_so_tmdt_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_so_tmdt_nonce_field'], 'import_so_tmdt_nonce' ) ) {
				wp_die( 'Nonce verification failed' );
			}

			$file = $_FILES['import_file']['tmp_name'];
			$spreadsheet = IOFactory::load( $file );
			$sheet = $spreadsheet->getActiveSheet();

			$highestRow = $sheet->getHighestRow();

			$nha_mang  = sanitize_text_field( $_POST['nha_mang'] );
			$loai_sim  = sanitize_text_field( $_POST['loai_sim'] );

			for ( $row = 2; $row <= $highestRow; $row++ ) {
				$name = trim( $sheet->getCell( 'B' . $row )->getValue() );

				if ( empty( $name ) ) {
					continue;
				}

				// check nếu Số TMDT đã tồn tại (title post type Số TMDT)
				$exists = get_page_by_title( $name, OBJECT, 'so-tmdt' );

				if ( $exists ) {
					$errors[] = $name;
					continue;
				}

				$post_id = wp_insert_post( [
					'post_title'  => $name,
					'post_type'   => 'so-tmdt',
					'post_status' => 'publish',
				] );

				if ( $post_id ) {

					update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $sheet->getCell( 'C' . $row )->getValue() ) );
					update_post_meta( $post_id, 'dinh_dang_sim', trim( $sheet->getCell( 'D' . $row )->getValue() ) );
					update_post_meta( $post_id, 'nha_mang', $nha_mang );
					update_post_meta( $post_id, 'loai_sim', $loai_sim );
					update_post_meta( $post_id, 'coc_sim', trim( $sheet->getCell( 'G' . $row )->getValue() ) );
					update_post_meta( $post_id, 'cam_ket', trim( $sheet->getCell( 'H' . $row )->getValue() ) );
					update_post_meta( $post_id, 'goi_cuoc', trim( $sheet->getCell( 'I' . $row )->getValue() ) );
					update_post_meta( $post_id, 'kenh_ban', trim( $sheet->getCell( 'J' . $row )->getValue() ) );
					update_post_meta( $post_id, 'tinh_trang_ban', trim( $sheet->getCell( 'K' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ma_don_hang', trim( $sheet->getCell( 'L' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ghi_chu', trim( $sheet->getCell( 'M' . $row )->getValue() ) );
				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}

	public function generate_xlsx() {
		if (
			isset( $_GET['post_type'] ) && $_GET['post_type'] === 'so-tmdt' &&
			isset( $_GET['page'] ) && $_GET['page'] === 'thong-tin-so-tmdt' &&
			isset( $_GET['export'] ) && $_GET['export'] === 'excel'
		) {
			$file = 'thong-tin-so-tmdt-' . date( 'd-m-Y' ) . '.xlsx';

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			// Header
			$headers = [
				'STT',
				'SDT - Định dạng thường',
				'SDT - Chấm định dạng',
				'Định dạng sim',
				'Nhà mạng',
				'Loại sim',
				'Cọc sim',
				'Cam kết',
				'Gói cước',
				'Kênh bán hàng',
				'Tình trạng bán hàng',
				'Mã đơn hàng',
				'Ghi chú',
				'Gán Serial Sim',
			];
			$sheet->fromArray( $headers, null, 'A1' );

			// Rows
			$rowIndex = 2;

			$data_serial = PostType::get_data();

			foreach ( $data_serial as $key => $nv ) {
				$sheet->fromArray( [
					esc_html( $key + 1 ),
					esc_html( $nv['sdt'] ),
					esc_html( $nv['sdt_chamdinhdang'] ),
					esc_html( $nv['dinh_dang_sim'] ),
					esc_html( $nv['nha_mang'] ),
					esc_html( $nv['loai_sim'] ),
					esc_html( $nv['coc_sim'] ),
					esc_html( $nv['cam_ket'] ),
					esc_html( $nv['goi_cuoc'] ),
					esc_html( $nv['kenh_ban'] ),
					esc_html( $nv['tinh_trang_ban'] ),
					esc_html( $nv['ma_don_hang'] ),
					esc_html( $nv['ghi_chu'] ),
					esc_html( $nv['serial_sim'] ),
				], null, 'A' . $rowIndex );

				$rowIndex++;
			}

			$writer = new Xlsx( $spreadsheet );
			ob_end_clean();

			// Xuất file
			header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
			header( 'Content-Disposition: attachment;filename="' . $file . '"' );
			header( 'Cache-Control: max-age=0' );
			// If you're serving to IE 9, then the following may be needed
			header( 'Cache-Control: max-age=1' );

			$writer->save( 'php://output' );
			exit;
		}
	}
}
