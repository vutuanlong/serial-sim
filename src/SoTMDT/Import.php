<?php

namespace ASS\SoTMDT;

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
			'edit.php?post_type=so-tmdt',
			'Nhập Số TMDT',
			'Nhập Số TMDT',
			'manage_options',
			'import-so-tmdt',
			[ $this, 'import_so_tmdt_excel' ]
		);
	}

	public function import_so_tmdt_excel() {
		echo '<div class="wrap"><h1>Nhập Số TMDT</h1>';

		// Form upload file
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'import_so_tmdt_nonce', 'import_so_tmdt_nonce_field' );

		?>

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

		<p>
			<label>Chọn loại sim:</label><br>
			<select name="loai_sim" required>
				<option value="Trả trước">Trả trước</option>
				<option value="Trả sau">Trả sau</option>
			</select>
		</p>

		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
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
					update_post_meta( $post_id, 'cam_ket', trim( $sheet->getCell( 'G' . $row )->getValue() ) );
					update_post_meta( $post_id, 'goi_cuoc', trim( $sheet->getCell( 'H' . $row )->getValue() ) );
					update_post_meta( $post_id, 'kenh_ban', trim( $sheet->getCell( 'I' . $row )->getValue() ) );
					update_post_meta( $post_id, 'tinh_trang_ban', trim( $sheet->getCell( 'LJ' . $row )->getValue() ) );
					update_post_meta( $post_id, 'ghi_chu', trim( $sheet->getCell( 'K' . $row )->getValue() ) );
				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}
}
