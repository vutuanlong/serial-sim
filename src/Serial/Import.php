<?php

namespace ASS\Serial;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\IOFactory;

use ASS\Helper;

class Import {
	public function init() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=serial',
			'Nhập Serial',
			'Nhập Serial',
			'manage_options',
			'import-serial',
			[ $this, 'import_serial_excel' ]
		);
	}

	public function import_serial_excel() {
		echo '<div class="wrap"><h1>Nhập Serial</h1>';

		// Form upload file
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'import_serial_nonce', 'import_serial_nonce_field' );

		$array_nha_mang = Helper::nha_mang();
		?>
		<p>
			<label>Chọn ngày nhập:</label><br>
			<input type="date" name="ngay_nhap" required>
		</p>

		<p>
			<label>Chọn nhà mạng:</label><br>
			<select name="nha_mang" required>
				<?php foreach ( $array_nha_mang as $nha_mang ) : ?>
					<option value="<?= $nha_mang ?>"><?= $nha_mang ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
		echo '<p>Tải file mẫu <a href="' . ASS_URL . 'import-serial.xlsx">tại đây</a></p>';
		submit_button( 'Upload và Nhập Serial' );
		echo '</form>';

		// Xử lý file upload
		if ( isset( $_FILES['import_file'] ) && ! empty( $_FILES['import_file']['tmp_name'] ) ) {
			if ( ! isset( $_POST['import_serial_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_serial_nonce_field'], 'import_serial_nonce' ) ) {
				wp_die( 'Nonce verification failed' );
			}

			$file = $_FILES['import_file']['tmp_name'];
			$spreadsheet = IOFactory::load( $file );
			$sheet = $spreadsheet->getActiveSheet();

			$highestRow = $sheet->getHighestRow();

			$ngay_nhap = sanitize_text_field( $_POST['ngay_nhap'] );
			$nha_mang  = sanitize_text_field( $_POST['nha_mang'] );

			for ( $row = 2; $row <= $highestRow; $row++ ) {
				$date = trim( $sheet->getCell( 'B' . $row )->getValue() );
				$name = trim( $sheet->getCell( 'C' . $row )->getValue() );

				if ( empty( $name ) && empty( $date ) ) {
					continue;
				}

				// check nếu serial đã tồn tại (title post type serial)
				$exists = get_page_by_title( $name, OBJECT, 'serial' );

				if ( $exists ) {
					$errors[] = $name;
					continue;
				}

				$post_id = wp_insert_post( [
					'post_title'  => $name,
					'post_type'   => 'serial',
					'post_status' => 'publish',
				] );

				if ( $post_id ) {

					update_post_meta( $post_id, 'ngay_nhap', $ngay_nhap );
					update_post_meta( $post_id, 'sdt', trim( $sheet->getCell( 'D' . $row )->getValue() ) );
					update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $sheet->getCell( 'E' . $row )->getValue() ) );
					update_post_meta( $post_id, 'dinh_dang_sim', trim( $sheet->getCell( 'F' . $row )->getValue() ) );
					update_post_meta( $post_id, 'nha_mang', $nha_mang );
					update_post_meta( $post_id, 'loai_sim', trim( $sheet->getCell( 'H' . $row )->getValue() ) );
					update_post_meta( $post_id, 'cam_ket', trim( $sheet->getCell( 'I' . $row )->getValue() ) );
					update_post_meta( $post_id, 'goi_cuoc', trim( $sheet->getCell( 'J' . $row )->getValue() ) );
					update_post_meta( $post_id, 'kenh_ban', trim( $sheet->getCell( 'K' . $row )->getValue() ) );
					update_post_meta( $post_id, 'tinh_trang_ban', trim( $sheet->getCell( 'L' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ghi_chu', trim( $sheet->getCell( 'M' . $row )->getValue() ) );
				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}
}
