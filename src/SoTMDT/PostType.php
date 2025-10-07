<?php

namespace ASS\SoTMDT;

class PostType {
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public static function nha_mang() {
		return [
			"Viettel",
			"Vinaphone",
			"Mobifone",
			"Vietnamobile",
			"Itel",
			"Local",
			"Vnsky",
			"Wintel"
		];
	}

	public function register_post_type() {
		$labels  = [
			'name'               => __( 'Số TMDT', 'ass' ),
			'singular_name'      => __( 'Số TMDT', 'ass' ),
			'add_new'            => _x( 'Thêm Số TMDT mới', 'Số TMDT', 'ass' ),
			'add_new_item'       => __( 'Thêm Số TMDT mới', 'ass' ),
			'edit_item'          => __( 'Sửa Số TMDT', 'ass' ),
			'new_item'           => __( 'Số TMDT mới', 'ass' ),
			'view_item'          => __( 'Xem Số TMDT', 'ass' ),
			'view_items'         => __( 'Xem Số TMDT', 'ass' ),
			'search_items'       => __( 'Tìm kiếm Số TMDT', 'ass' ),
			'not_found'          => __( 'Không có Số TMDT.', 'ass' ),
			'not_found_in_trash' => __( 'Không có Số TMDT trong thùng rác.', 'ass' ),
			'parent_item_colon'  => __( 'Parent Products:', 'ass' ),
			'all_items'          => __( 'Tất cả Số TMDT', 'ass' ),
		];

		$args = [
			'label'       => __( 'Số TMDT', 'ass' ),
			'labels'      => $labels,
			'supports'    => [ 'title', 'excerpt', 'author' ],
			'public'      => false,
			'show_ui'     => true,
			'has_archive' => false,
			'hierarchical' => false,
			'menu_icon'   => 'dashicons-phone',
			'rewrite'     => [ 'slug' => 'so-tmdt' ],
		];

		register_post_type( 'so-tmdt', $args );
	}

	public function add_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			$page = add_submenu_page(
				'edit.php?post_type=so-tmdt',
				__( 'Xem thông tin', 'ass' ),
				__( 'Xem thông tin', 'ass' ),
				'manage_options',
				'thong-tin-so-tmdt',
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
			include ASS_DIR . 'templates/admin/so-tmdt-views.php';
		} else {
			wp_die( 'Bạn không có quyền truy cập trang này' );
		}
	}

	public function enqueue() {
		// wp_enqueue_style( 'baocao', trailingslashit( ASS_URL ) . "assets/css/baocao.css", [], filemtime( trailingslashit( ASS_DIR ) . "assets/css/baocao.css" ) );

		wp_enqueue_style( 'serial', trailingslashit( ASS_URL ) . "assets/css/serial.css", [], filemtime( trailingslashit( ASS_DIR ) . "assets/css/serial.css" ) );
		wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0' );

		wp_enqueue_script( 'serial-admin', ASS_URL . '/assets/js/serial-admin.js', ['jquery'], '1.0', true );
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true );
		wp_localize_script(
			'serial-admin',
			'ajax_object',
			[ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
		);
	}

	public static function get_data() {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'chi_phi_don';
		$order   = isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : 'DESC';

		$args = [
			'post_type'      => 'so-tmdt',
			'post_status'    => 'publish',
			'posts_per_page' => -1, // Lấy hết để tính toán
			'no_found_rows'  => false, // cần false để WP_Query đếm tổng post
		];

		$query = new \WP_Query( $args );

		$data_so_tmdt = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id      = get_the_ID();

				$data_so_tmdt[] = [
					'so_tmdt_id' => $post_id,
					'sdt' => get_the_title( $post_id ),
					'sdt_chamdinhdang'  => get_post_meta( $post_id, 'sdt_chamdinhdang', true ),
					'dinh_dang_sim'  => get_post_meta( $post_id, 'dinh_dang_sim', true ),
					'nha_mang'  => get_post_meta( $post_id, 'nha_mang', true ),
					'loai_sim'  => get_post_meta( $post_id, 'loai_sim', true ),
					'cam_ket'  => get_post_meta( $post_id, 'cam_ket', true ),
					'goi_cuoc'  => get_post_meta( $post_id, 'goi_cuoc', true ),
					'kenh_ban'  => get_post_meta( $post_id, 'kenh_ban', true ),
					'tinh_trang_ban'  => get_post_meta( $post_id, 'tinh_trang_ban', true ),
					'ghi_chu'  => get_post_meta( $post_id, 'ghi_chu', true ),
					'serial_sim'  => get_post_meta( $post_id, 'serial_sim', true ),
				];
			}
		}
		wp_reset_postdata();

		return $data_so_tmdt;
	}
}
