<?php

namespace ASS\SoWeb;

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
			'edit.php?post_type=so-web',
			'Nhập Số Web',
			'Nhập Số Web',
			'manage_options',
			'import-so-web',
			[ $this, 'import_so_web_excel' ]
		);
	}

	public function import_so_web_excel() {
		echo '<div class="wrap"><h1>Nhập Số Web</h1>';

		// Form upload file
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'import_so_web_nonce', 'import_so_web_nonce_field' );

		?>

		<p>
			<label>ID Kho:</label><br>
			<select name="id_kho" required>
				<option value="khovt">Viettel</option>
				<option value="khovnph">Vinaphone</option>
				<option value="khomobi">Mobifone</option>
			</select>
		</p>

		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
		submit_button( 'Upload và Nhập Số Web' );
		echo '</form>';

		// Xử lý file upload
		if ( isset( $_FILES['import_file'] ) && ! empty( $_FILES['import_file']['tmp_name'] ) ) {
			if ( ! isset( $_POST['import_so_web_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_so_web_nonce_field'], 'import_so_web_nonce' ) ) {
				wp_die( 'Nonce verification failed' );
			}

			$file = $_FILES['import_file']['tmp_name'];
			$spreadsheet = IOFactory::load( $file );
			$sheet = $spreadsheet->getActiveSheet();

			$highestRow = $sheet->getHighestRow();

			$id_kho  = sanitize_text_field( $_POST['id_kho'] );

			for ( $row = 2; $row <= $highestRow; $row++ ) {
				$name = trim( $sheet->getCell( 'B' . $row )->getValue() );

				if ( empty( $name ) ) {
					continue;
				}

				// check nếu so-web đã tồn tại (title post type so-web)
				$exists = get_page_by_title( $name, OBJECT, 'so-web' );

				if ( $exists ) {
					$errors[] = $name;
					continue;
				}

				$post_id = wp_insert_post( [
					'post_title'  => $name,
					'post_type'   => 'so-web',
					'post_status' => 'publish',
				] );

				if ( $post_id ) {
					update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $sheet->getCell( 'C' . $row )->getValue() ) );
					update_post_meta( $post_id, 'id_kho', $id_kho );
					update_post_meta( $post_id, 'coc_sim', trim( $sheet->getCell( 'E' . $row )->getValue() ) );
					update_post_meta( $post_id, 'gia_ban_le', trim( $sheet->getCell( 'F' . $row )->getValue() ) );
					update_post_meta( $post_id, 'gia_dai_ly', trim( $sheet->getCell( 'G' . $row )->getValue() ) );
					update_post_meta( $post_id, 'loai_sim', trim( $sheet->getCell( 'H' . $row )->getValue() ) );
					update_post_meta( $post_id, 'cam_ket', trim( $sheet->getCell( 'I' . $row )->getValue() ) );
					update_post_meta( $post_id, 'kenh_ban', trim( $sheet->getCell( 'J' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ngay_ban', trim( $sheet->getCell( 'K' . $row )->getValue() ) );
					update_post_meta( $post_id, 'tinh_trang_ban', trim( $sheet->getCell( 'L' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ghi_chu', trim( $sheet->getCell( 'M' . $row )->getValue() ) );
				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}
}
