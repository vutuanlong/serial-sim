<?php

namespace ASS\Serial;

class PostType {
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'rwmb_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'wp_ajax_save_so_tmdt_inline', [ $this, 'save_so_tmdt_inline' ] );
	}


	public function register_post_type() {
		$labels  = [
			'name'               => __( 'Serial số', 'ass' ),
			'singular_name'      => __( 'Serial số', 'ass' ),
			'add_new'            => _x( 'Thêm Serial số mới', 'Serial số', 'ass' ),
			'add_new_item'       => __( 'Thêm Serial số mới', 'ass' ),
			'edit_item'          => __( 'Sửa Serial số', 'ass' ),
			'new_item'           => __( 'Serial số mới', 'ass' ),
			'view_item'          => __( 'Xem Serial số', 'ass' ),
			'view_items'         => __( 'Xem Serial số', 'ass' ),
			'search_items'       => __( 'Tìm kiếm Serial số', 'ass' ),
			'not_found'          => __( 'Không có Serial số.', 'ass' ),
			'not_found_in_trash' => __( 'Không có Serial số trong thùng rác.', 'ass' ),
			'parent_item_colon'  => __( 'Parent Products:', 'ass' ),
			'all_items'          => __( 'Tất cả Serial số', 'ass' ),
		];

		$args = [
			'label'       => __( 'Serial số', 'ass' ),
			'labels'      => $labels,
			'supports'    => [ 'title', 'excerpt', 'author' ],
			'public'      => false,
			'show_ui'     => true,
			'has_archive' => false,
			'hierarchical' => false,
			'menu_icon'   => 'dashicons-phone',
			'rewrite'     => [ 'slug' => 'serial' ],
		];

		register_post_type( 'serial', $args );
	}

	public function add_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			$page = add_submenu_page(
				'edit.php?post_type=serial',
				__( 'Thông tin serial sim + kho số TMDT', 'ass' ),
				__( 'Thông tin serial sim + kho số TMDT', 'ass' ),
				'manage_options',
				'thong-tin',
				[
					$this,
					'render',
				]
			);
			add_action( "admin_print_styles-$page", [ $this, 'enqueue' ] );
		}
	}

	public function render() {

		$current_user = wp_get_current_user();

		if (
			in_array( 'administrator', $current_user->roles )
		) {
			include ASS_DIR . 'templates/admin/serial-views.php';
		} else {
			wp_die( 'Bạn không có quyền truy cập trang này' );
		}
	}

	public function enqueue() {
		wp_enqueue_style( 'serial', trailingslashit( ASS_URL ) . "assets/css/serial.css", [], filemtime( trailingslashit( ASS_DIR ) . "assets/css/serial.css" ) );
		wp_enqueue_script( 'serial-admin', ASS_URL . '/assets/js/serial-admin.js', ['jquery'], '1.0', true );
		wp_localize_script(
			'serial-admin',
			'ajax_object',
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
		);
	}

	public static function serial_get_data() {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'chi_phi_don';
		$order   = isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : 'DESC';

		$args = [
			'post_type'      => 'serial',
			'post_status'    => 'publish',
			'posts_per_page' => -1, // Lấy hết để tính toán
			'no_found_rows'  => false, // cần false để WP_Query đếm tổng post
		];

		$query = new \WP_Query( $args );

		$data_serial = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id      = get_the_ID();

				$data_serial[] = [
					'serial_id' => $post_id,
					'serial_sim' => get_the_title( $post_id ),
					'ngay_nhap'  => get_post_meta( $post_id, 'ngay_nhap', true ),
					'sdt' => get_post_meta( $post_id, 'sdt', true ),
					'sdt_chamdinhdang'  => get_post_meta( $post_id, 'sdt_chamdinhdang', true ),
					'dinh_dang_sim'  => get_post_meta( $post_id, 'dinh_dang_sim', true ),
					'nha_mang'  => get_post_meta( $post_id, 'nha_mang', true ),
					'loai_sim'  => get_post_meta( $post_id, 'loai_sim', true ),
					'cam_ket'  => get_post_meta( $post_id, 'cam_ket', true ),
					'goi_cuoc'  => get_post_meta( $post_id, 'goi_cuoc', true ),
					'kenh_ban'  => get_post_meta( $post_id, 'kenh_ban', true ),
					'tinh_trang_ban'  => get_post_meta( $post_id, 'tinh_trang_ban', true ),
					'ghi_chu'  => get_post_meta( $post_id, 'ghi_chu', true ),
				];
			}
		}
		wp_reset_postdata();

		return $data_serial;
	}

	public function save_so_tmdt_inline() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Bạn không có quyền' ] );
		}

		$post_id = intval( $_POST['post_id'] );

		// Update meta
		$fields = [
			'sdt',
			'sdt_chamdinhdang',
			'dinh_dang_sim',
			'nha_mang',
			'loai_sim',
			'coc_sim',
			'cam_ket',
			'goi_cuoc',
			'kenh_ban',
			'tinh_trang_ban',
			'ma_don_hang',
			'ghi_chu',
			'serial_sim',
		];
		foreach ( $fields as $field ) {
			if ( isset( $_POST[$field] ) ) {
				update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
			}
		}

		if ( ! empty( $_POST['sdt'] ) ) {
			wp_update_post( [
				'ID' => $post_id,
				'post_title' => sanitize_text_field( $_POST['sdt'] ),
			] );
		}

		if ( ! empty( $_POST['serial_sim'] ) ) {

			$query = new \WP_Query( [
				'post_type'      => 'serial',
				'title'          => $_POST['serial_sim'],
				'posts_per_page' => 1,
				'fields'         => 'ids'
			] );

			$serial_sim = $query->posts[0];
			update_post_meta( $serial_sim, 'sdt', sanitize_text_field( $_POST['sdt'] ) );
		}

		wp_send_json_success( [ 'message' => 'Lưu thành công' ] );
	}

	public function register_meta_boxes( $meta_boxes ) {
		$product_id   = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';
		$meta_boxes[] = [
			'id'         => 'product_info',
			'title'      => 'Thông tin ca live',
			'post_types' => [ 'serial' ],
			'fields'     => [
				[
					'id'       => 'date_live',
					'type'     => 'date',
					'name'     => 'Ngày live',
					'columns'  => 6,
					'required' => 1,
					'js_options' => [
						'dateFormat' => 'dd/mm/yy',
					],
					'save_format' => 'Y-m-d',
				],
				[
					'id'       => 'hour_start_live',
					'name'     => 'Giờ bắt đầu live',
					'columns'  => 6,
					'required' => 1,
					'type'     => 'time',
				],
				[
					'id'       => 'time_count',
					'name'     => 'Thời lượng live',
					'desc'     => esc_html__( 'Đơn vị là phút', 'aml' ),
					'columns'  => 6,
					'required' => 1,
				],
				[
					'id'       => 'gmv',
					'name'     => 'GMV(tổng số tiền bán được)',
					'desc'     => esc_html__( 'Đơn vị là đồng. Định dạng không có dấu chấm hoặc phẩy (ví dụ: 500000)', 'aml' ),
					'columns'  => 6,
					'required' => 1,
				],
				[
					'id'       => 'salary_per_live',
					'name'     => 'Công phiên live',
					'columns'  => 6,
					'desc'     => esc_html__( 'Đơn vị là đồng/giờ. Định dạng không có dấu chấm hoặc phẩy (ví dụ: 500000)', 'aml' ),
					'required' => 1,
				],
				[
					'id'       => 'number_of_order_per_live',
					'name'     => 'Số lượng đơn phiên live',
					'columns'  => 6,
					'required' => 1,
				],

				[
					'type'     => 'file',
					'id'       => 'image_live',
					'name'     => 'Ảnh phiên live',
					'columns'  => 6,
					'required' => 1,
				],
			],
		];

		return $meta_boxes;
	}

}
