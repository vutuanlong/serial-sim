<?php

namespace ASS\SoWeb;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

use PhpOffice\PhpSpreadsheet\IOFactory;

use ASS\Helper;

class Import {
	public function init() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		add_action( 'wp_ajax_import_so_web', [ $this, 'import_so_web' ] );
		add_action( 'wp_ajax_import_so_web_batch', [ $this, 'import_so_web_batch' ] );
	}

	public function admin_menu() {
		$page = add_submenu_page(
			'edit.php?post_type=so-web',
			'Nhập Số Web',
			'Nhập Số Web',
			'manage_options',
			'import-so-web',
			[ $this, 'import_so_web_excel_form' ]
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

	public function import_so_web_excel_form() {
		echo '<div class="wrap"><h1>Nhập Số Web</h1>';

		// Form upload file
		echo '<form id="import-so-web-form" method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'import_so_web_nonce', 'import_so_web_nonce_field' );

		$array_nha_mang = Helper::nha_mang();
		?>

		<p>
			<label>ID Kho:</label><br>
			<select name="id_kho" required>
				<?php foreach ( $array_nha_mang as $nha_mang ) : ?>
					<option value="<?= $nha_mang ?>"><?= $nha_mang ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<?php

		echo '<input type="file" name="import_file" accept=".xlsx" required />';
		echo '<p>Tải file mẫu <a href="' . ASS_URL . 'import-so-web.xlsx">tại đây</a></p>';
		submit_button( 'Upload và Nhập Số Web' );
		echo '<input type="hidden" name="action" value="import_so_web">';
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

	public function import_so_web() {

		// Xử lý file upload
		if ( empty( $_FILES['import_file'] ) ) {
			wp_send_json( [ 'status' => 'error' ] );
		}

		if ( ! isset( $_POST['import_so_web_nonce_field'] ) || ! wp_verify_nonce( $_POST['import_so_web_nonce_field'], 'import_so_web_nonce' ) ) {
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

	public function import_so_web_batch() {
		$import_id = $_POST['import_id'];
		$offset    = intval( $_POST['offset'] );

		$rows = get_transient( $import_id );
		$total = count( $rows );

		$batch_size = 50;

		$end = $offset + $batch_size;
		if ( $end > $total ) $end = $total;

		$id_kho = isset( $_POST['id_kho'] ) ? sanitize_text_field( $_POST['id_kho'] ) : '';

		// Xử lý từng dòng CSV
		for ( $i = $offset; $i < $end; $i++ ) {

			$row = $rows[ $i ];
			$name = trim( $row[1] );

			if ( empty( $name ) ) {
				continue;
			}

			// check nếu so-web đã tồn tại (title post type so-web)
			$exists = get_page_by_title( $name, OBJECT, 'so-web' );

			if ( $exists ) {
				$errors[] = $name;
				continue;
			}

			$post_date = current_time( 'mysql' );
			$post_date = date( 'Y-m-d H:i:s', strtotime( $post_date ) - ( 30 * 60 ) + $i );

			$post_id = wp_insert_post( [
				'post_title'  => $name,
				'post_type'   => 'so-web',
				'post_status' => 'publish',
				'post_date' => $post_date,
			] );

			if ( $post_id ) {
				update_post_meta( $post_id, 'sdt_chamdinhdang', trim( $row[2] ) );
				update_post_meta( $post_id, 'id_kho', $id_kho );
				update_post_meta( $post_id, 'dinh_dang_sim', trim( $row[4] ) );
				update_post_meta( $post_id, 'nha_mang', trim( $row[5] ) );
				update_post_meta( $post_id, 'loai_sim', trim( $row[6] ) );
				update_post_meta( $post_id, 'coc_sim', trim( $row[7] ) );
				update_post_meta( $post_id, 'gia_ban_le', trim( $row[8] ) );
				update_post_meta( $post_id, 'gia_dai_ly', trim( $row[9] ) );
				update_post_meta( $post_id, 'cam_ket', trim( $row[10] ) );
				update_post_meta( $post_id, 'goi_cuoc', trim( $row[11] ) );
				update_post_meta( $post_id, 'kenh_ban', trim( $row[12] ) );
				update_post_meta( $post_id, 'ngay_ban', trim( $row[13] ) );
				update_post_meta( $post_id, 'tinh_trang_ban', trim( $row[14] ) );
				update_post_meta( $post_id, 'ghi_chu', trim( $row[15] ) );
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
}
