<?php

namespace ASS\Serial;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\IOFactory;

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

		?>
		<p>
			<label>Chọn ngày nhập:</label><br>
			<input type="date" name="ngay_nhap" required>
		</p>

		<p>
			<label>Chọn nhà mạng:</label><br>
			<select name="nha_mang" required>
				<option value="Viettel">Viettel</option>
				<option value="Vinaphone">Vinaphone</option>
				<option value="Mobifone">Mobifone</option>
				<option value="Vietnamobile">Vietnamobile</option>
				<option value="Itel">Itel</option>
				<option value="Local">Local</option>
				<option value="Vnsky">Vnsky</option>
				<option value="Wintel">Wintel</option>
			</select>
		</p>
		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
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
					update_post_meta( $post_id, 'nha_mang', $nha_mang );
				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}
}
