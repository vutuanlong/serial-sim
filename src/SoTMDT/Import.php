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

		add_action( 'wp_ajax_import_so_tmdt', [ $this, 'import_so_tmdt' ] );
		add_action( 'wp_ajax_import_batch', [ $this, 'import_batch' ] );

		add_action( 'wp_ajax_import_so_tmdt_serial', [ $this, 'import_so_tmdt_serial' ] );
		add_action( 'wp_ajax_import_so_tmdt_serial_batch', [ $this, 'import_so_tmdt_serial_batch' ] );
	}

	public function admin_menu() {
		$page = add_submenu_page(
			'edit.php?post_type=so-tmdt',
			'Nhập Số TMDT',
			'Nhập Số TMDT',
			'manage_options',
			'import-so-tmdt',
			[ $this, 'import_so_tmdt_excel_form' ]
		);
		add_action( "admin_print_styles-$page", [ $this, 'enqueue' ] );

		$page_ghep = add_submenu_page(
			'edit.php?post_type=so-tmdt',
			'Nhập Số TMDT + Serial Sim đã ghép',
			'Nhập Số TMDT + Serial Sim đã ghép',
			'manage_options',
			'import-so-tmdt-serial',
			[ $this, 'import_so_tmdt_serial_form' ]
		);
		add_action( "admin_print_styles-$page_ghep", [ $this, 'enqueue' ] );
	}

	public function enqueue() {

		// wp_enqueue_style( 'serial', trailingslashit( ASS_URL ) . "assets/css/serial.css", [], filemtime( trailingslashit( ASS_DIR ) . "assets/css/serial.css" ) );
		// wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0' );

		wp_enqueue_script( 'serial-admin', ASS_URL . '/assets/js/serial-admin.js', ['jquery'], '1.0', true );
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true );
		wp_localize_script(
			'serial-admin',
			'ajax_object',
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
		);
	}

	public function import_so_tmdt_serial_form() {
		echo '<div class="wrap"><h1>Nhập Số TMDT + Serial Sim đã ghép</h1>';

		// Form upload file
		echo '<form id="import-so-tmdt-serial-form" method="post" enctype="multipart/form-data">';
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
		echo '<input type="hidden" name="action" value="import_so_tmdt_serial">';
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

	public function import_so_tmdt_serial() {

		// Xử lý file upload
		if ( empty( $_FILES['import_file'] ) ) {
			wp_send_json( [ 'status' => 'error' ] );
		}

		if ( ! isset( $_POST['import_so_tmdt_serial_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_so_tmdt_serial_nonce_field'], 'import_so_tmdt_serial_nonce' ) ) {
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

	public function import_so_tmdt_serial_batch() {
		$import_id = $_POST['import_id'];
		$offset    = intval( $_POST['offset'] );

		$rows = get_transient( $import_id );
		$total = count( $rows );

		$batch_size = 50;

		$end = $offset + $batch_size;
		if ( $end > $total ) $end = $total;

		$nha_mang = isset( $_POST['nha_mang'] ) ? sanitize_text_field( $_POST['nha_mang'] ) : '';
		$loai_sim = isset( $_POST['loai_sim'] ) ? sanitize_text_field( $_POST['loai_sim'] ) : '';

		// Xử lý từng dòng CSV
		for ( $i = $offset; $i < $end; $i++ ) {

			$row = $rows[ $i ];

			$name_so_tmdt = trim( $row[3] );
			$name_serial  = trim( $row[2] );

			if ( empty( $name_so_tmdt ) ) {
				continue;
			}

			// check nếu Số TMDT đã tồn tại (title post type Số TMDT)
			$exists = get_page_by_title( $name_so_tmdt, OBJECT, 'so-tmdt' );

			if ( $exists ) {
				$errors[] = $name_so_tmdt;
				continue;
			}

			$post_date = current_time( 'mysql' );
			$post_date = date( 'Y-m-d H:i:s', strtotime( $post_date ) - ( 30 * 60 ) + $i );

			$post_id = wp_insert_post( [
				'post_title'  => $name_so_tmdt,
				'post_type'   => 'so-tmdt',
				'post_status' => 'publish',
				'post_date' => $post_date,
			] );

			if ( $post_id ) {

				update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $row[4] ) );
				update_post_meta( $post_id, 'dinh_dang_sim', trim( $row[5] ) );
				update_post_meta( $post_id, 'nha_mang', $nha_mang );
				update_post_meta( $post_id, 'loai_sim', $loai_sim );
				update_post_meta( $post_id, 'coc_sim', trim( $row[8] ) );
				update_post_meta( $post_id, 'cam_ket', trim( $row[9] ) );
				update_post_meta( $post_id, 'goi_cuoc', trim( $row[10] ) );
				update_post_meta( $post_id, 'kenh_ban', trim( $row[11] ) );
				update_post_meta( $post_id, 'tinh_trang_ban', trim( $row[12] ) );
				update_post_meta( $post_id, 'ma_don_hang', trim( $row[13] ) );
				update_post_meta( $post_id, 'ghi_chu', trim( $row[14] ) );
				update_post_meta( $post_id, 'serial_sim', $name_serial );
			}

			$post_id_serial = wp_insert_post( [
				'post_title'  => $name_serial,
				'post_type'   => 'serial',
				'post_status' => 'publish',
			] );
			if ( $post_id_serial ) {
				update_post_meta( $post_id_serial, 'sdt', sanitize_text_field( $name_so_tmdt ) );
				update_post_meta( $post_id_serial, 'ngay_nhap', trim( $row[1] ) );
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

	public function import_so_tmdt_excel_form() {
		echo '<div class="wrap"><h1>Nhập Số TMDT</h1>';

		// Form upload file
		echo '<form id="import-so-tmdt-form" method="post" enctype="multipart/form-data">';
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
		echo '<input type="hidden" name="action" value="import_so_tmdt">';
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

	public function import_so_tmdt() {

		// Xử lý file upload
		if ( empty( $_FILES['import_file'] ) ) {
			wp_send_json( [ 'status' => 'error' ] );
		}

		if ( ! isset( $_POST['import_so_tmdt_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_so_tmdt_nonce_field'], 'import_so_tmdt_nonce' ) ) {
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

		// echo '<div class="updated notice"></div>';
	}

	public function import_batch() {
		$import_id = $_POST['import_id'];
		$offset    = intval( $_POST['offset'] );

		$rows = get_transient( $import_id );
		$total = count( $rows );

		$batch_size = 50;

		$end = $offset + $batch_size;
		if ( $end > $total ) $end = $total;

		$nha_mang = isset( $_POST['nha_mang'] ) ? sanitize_text_field( $_POST['nha_mang'] ) : '';
		$loai_sim = isset( $_POST['loai_sim'] ) ? sanitize_text_field( $_POST['loai_sim'] ) : '';

		// Xử lý từng dòng CSV
		for ( $i = $offset; $i < $end; $i++ ) {
			$row = $rows[ $i ];

			$name = trim( $row[1] );

			if ( empty( $name ) ) {
				continue;
			}

			// check nếu Số TMDT đã tồn tại (title post type Số TMDT)
			$exists = get_page_by_title( $name, OBJECT, 'so-tmdt' );

			if ( $exists ) {
				$errors[] = $name;
				continue;
			}

			$post_date = current_time( 'mysql' );
			$post_date = date( 'Y-m-d H:i:s', strtotime( $post_date ) - ( 30 * 60 ) + $i );

			$post_id = wp_insert_post( [
				'post_title'  => $name,
				'post_type'   => 'so-tmdt',
				'post_status' => 'publish',
				'post_date' => $post_date,
			] );

			if ( $post_id ) {
				update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $row[2] ) );
				update_post_meta( $post_id, 'dinh_dang_sim', trim( $row[3] ) );
				update_post_meta( $post_id, 'nha_mang', $nha_mang );
				update_post_meta( $post_id, 'loai_sim', $loai_sim );
				update_post_meta( $post_id, 'coc_sim', trim( $row[6] ) );
				update_post_meta( $post_id, 'cam_ket', trim( $row[7] ) );
				update_post_meta( $post_id, 'goi_cuoc', trim( $row[8] ) );
				update_post_meta( $post_id, 'kenh_ban', trim( $row[9] ) );
				update_post_meta( $post_id, 'tinh_trang_ban', trim( $row[10] ) );
				update_post_meta( $post_id, 'ma_don_hang', trim( $row[11] ) );
				update_post_meta( $post_id, 'ghi_chu', trim( $row[12] ) );
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
