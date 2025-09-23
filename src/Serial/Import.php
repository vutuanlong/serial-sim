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
			[$this, 'import_serial_excel']
		);
	}

	public function import_serial_excel() {
		echo '<div class="wrap"><h1>Nhập Serial</h1>';

		// Form upload file
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field('import_serial_nonce', 'import_serial_nonce_field');
		echo '<input type="file" name="import_file" accept=".xlsx" required />';
		submit_button('Upload và Nhập Serial');
		echo '</form>';

		// Xử lý file upload
		if (isset($_FILES['import_file']) && !empty($_FILES['import_file']['tmp_name'])) {
			if (!isset($_POST['import_serial_nonce_field']) || !wp_verify_nonce($_POST['import_serial_nonce_field'], 'import_serial_nonce')) {
				wp_die('Nonce verification failed');
			}

			$file = $_FILES['import_file']['tmp_name'];
			$spreadsheet = IOFactory::load($file);
			$sheet = $spreadsheet->getActiveSheet();

			$highestRow = $sheet->getHighestRow();

			for ($row = 4; $row <= $highestRow; $row++) {
				$name          = trim($sheet->getCell('A' . $row)->getValue());
				$email         = trim($sheet->getCell('B' . $row)->getValue());
				$response_time = trim($sheet->getCell('C' . $row)->getValue());

				if (empty($name) && empty($email)) {
					continue;
				}

				$post_id = wp_insert_post([
					'post_title'  => $name . ' - ' . $email . ' - ' . $response_time,
					'post_type'   => 'serial',
					'post_status' => 'publish'
				]);

				if ($post_id) {

					$contact_info = [
						'contact_name' => $name,
						'contact_email' => $email,
						'contact_city' => $response_time
					];

					update_post_meta($post_id, '_ppa_contact_info', $contact_info);

				}
			}

			echo '<div class="updated notice"><p>Import thành công!</p></div>';
		}

		echo '</div>';
	}
}
