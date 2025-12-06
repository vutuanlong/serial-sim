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
		add_action( 'admin_init', [ $this, 'generate_xlsx' ] );

		add_action( 'wp_ajax_import_serial', [ $this, 'import_serial' ] );
		add_action( 'wp_ajax_import_serial_batch', [ $this, 'import_serial_batch' ] );
	}

	public function admin_menu() {
		$page = add_submenu_page(
			'edit.php?post_type=serial',
			'Nhập Serial',
			'Nhập Serial',
			'manage_options',
			'import-serial',
			[ $this, 'import_serial_excel_form' ]
		);

		add_action( "admin_print_styles-$page", [ $this, 'enqueue' ] );
	}

	public function enqueue() {

		wp_enqueue_script( 'serial-admin', ASS_URL . '/assets/js/serial-admin.js', ['jquery'], '1.0', true );
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true );
		wp_localize_script(
			'serial-admin',
			'ajax_object',
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
		);
	}

	public function import_serial_excel_form() {
		echo '<div class="wrap"><h1>Nhập Serial</h1>';

		// Form upload file
		echo '<form id="import-serial-form" method="post" enctype="multipart/form-data">';
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
		echo '<input type="hidden" name="action" value="import_serial">';
		echo '</form>';

		?>
		<div id="import-progress-wrapper" style="display:none;margin-top:20px; width:300px;">
			<div style="background:#ddd; border-radius:3px;">
				<div id="progress-bar" style="height:20px; width:0%; background:#4caf50; transition:0.3s; border-radius:3px;"></div>
			</div>
			<div id="progress-text" style="margin-top:5px;">0%</div>
		</div>

		<div id="import-message" style="margin-top:10px; font-weight:bold;"></div>
		<?php

		echo '</div>';
	}

	public function import_serial() {

		// Xử lý file upload
		if ( empty( $_FILES['import_file'] ) ) {
			wp_send_json( [ 'status' => 'error' ] );
		}

		if ( ! isset( $_POST['import_serial_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_serial_nonce_field'], 'import_serial_nonce' ) ) {
			wp_die( 'Nonce verification failed' );
		}

		$file = $_FILES['import_file']['tmp_name'];
		$rows = [];

		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $file );
		$sheet = $spreadsheet->getActiveSheet();
		$rows = $sheet->toArray();
		$rows = array_slice( $rows, 1 );

		$import_id = 'import_' . wp_generate_uuid4();

		// Lưu toàn bộ CSV vào transient
		set_transient( $import_id, $rows, 60 * 60 );

		wp_send_json( [
			'status'    => 'ok',
			'import_id' => $import_id,
			'total'     => count( $rows ),
		] );
	}

	public function import_serial_batch() {
		$import_id = $_POST['import_id'];
		$offset    = intval( $_POST['offset'] );

		$rows = get_transient( $import_id );
		$total = count( $rows );

		$batch_size = 50;

		$end = $offset + $batch_size;
		if ( $end > $total ) $end = $total;

		$ngay_nhap_raw = isset( $_POST['ngay_nhap'] ) ? sanitize_text_field( $_POST['ngay_nhap'] ) : '';
		$nha_mang = isset( $_POST['nha_mang'] ) ? sanitize_text_field( $_POST['nha_mang'] ) : '';

		// Xử lý từng dòng CSV
		for ( $i = $offset; $i < $end; $i++ ) {

			$row = $rows[ $i ];

			$date = trim( $row[1] );
			$name = trim( $row[2] );

			if ( empty( $name ) && empty( $date ) ) {
				continue;
			}

			// check nếu serial đã tồn tại (title post type serial)
			$exists = get_page_by_title( $name, OBJECT, 'serial' );

			if ( $exists ) {
				$errors[] = $name;
				continue;
			}

			$post_date = current_time( 'mysql' );
			$post_date = date( 'Y-m-d H:i:s', strtotime( $post_date ) - ( 30 * 60 ) + $i );

			$post_id = wp_insert_post( [
				'post_title'  => $name,
				'post_type'   => 'serial',
				'post_status' => 'publish',
				'post_date' => $post_date,
			] );

			if ( $post_id ) {
				$ngay_nhap = \DateTime::createFromFormat( 'Y-m-d', $ngay_nhap_raw );
				update_post_meta( $post_id, 'ngay_nhap', $ngay_nhap ? $ngay_nhap->format( 'd/m/Y' ) : '' );
				// update_post_meta( $post_id, 'sdt', trim( $sheet->getCell( 'D' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $sheet->getCell( 'E' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'dinh_dang_sim', trim( $sheet->getCell( 'F' . $row )->getValue() ) );
				update_post_meta( $post_id, 'nha_mang', $nha_mang );
				// update_post_meta( $post_id, 'loai_sim', trim( $sheet->getCell( 'H' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'cam_ket', trim( $sheet->getCell( 'I' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'goi_cuoc', trim( $sheet->getCell( 'J' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'kenh_ban', trim( $sheet->getCell( 'K' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'tinh_trang_ban', trim( $sheet->getCell( 'L' . $row )->getValue() ) );
				// update_post_meta( $post_id, 'ghi_chu', trim( $sheet->getCell( 'M' . $row )->getValue() ) );
			}


		}

		$done = $end;
		$percent = round( ( $done / $total ) * 100 );

		$finished = ( $done >= $total );

		if ( $finished ) {
			delete_transient( $import_id );
		}

		wp_send_json( [
			"done"     => $done,
			"percent"  => $percent,
			"finished" => $finished,
		] );
	}

	public function generate_xlsx() {
		if (
			isset( $_GET['post_type'] ) && $_GET['post_type'] === 'serial' &&
			isset( $_GET['page'] ) && $_GET['page'] === 'thong-tin' &&
			isset( $_GET['export'] ) && $_GET['export'] === 'excel'
		) {
			$file = 'thong-tin-serial-sim-' . date( 'd-m-Y' ) . '.xlsx';

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			// Header
			$headers = [ 'STT', 'Ngày nhập Serial', 'Serial Sim', 'SDT' ];
			$sheet->fromArray( $headers, null, 'A1' );

			// Rows
			$rowIndex = 2;

			$data_serial = PostType::serial_get_data();

			foreach ( $data_serial as $key => $nv ) {
				$sheet->fromArray( [
					esc_html( $key + 1 ),
					esc_html( $nv['ngay_nhap'] ),
					esc_html( $nv['serial_sim'] ),
					esc_html( $nv['sdt'] ),
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
