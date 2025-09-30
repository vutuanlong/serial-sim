<?php

namespace ASS\SoWeb;

class PostType {
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}


	public function register_post_type() {
		$labels  = [
			'name'               => __( 'Số Web', 'ass' ),
			'singular_name'      => __( 'Số Web', 'ass' ),
			'add_new'            => _x( 'Thêm Số Web mới', 'Số Web', 'ass' ),
			'add_new_item'       => __( 'Thêm Số Web mới', 'ass' ),
			'edit_item'          => __( 'Sửa Số Web', 'ass' ),
			'new_item'           => __( 'Số Web mới', 'ass' ),
			'view_item'          => __( 'Xem Số Web', 'ass' ),
			'view_items'         => __( 'Xem Số Web', 'ass' ),
			'search_items'       => __( 'Tìm kiếm Số Web', 'ass' ),
			'not_found'          => __( 'Không có Số Web.', 'ass' ),
			'not_found_in_trash' => __( 'Không có Số Web trong thùng rác.', 'ass' ),
			'parent_item_colon'  => __( 'Parent Products:', 'ass' ),
			'all_items'          => __( 'Tất cả Số Web', 'ass' ),
		];

		$args = [
			'label'       => __( 'Số Web', 'ass' ),
			'labels'      => $labels,
			'supports'    => [ 'title', 'excerpt', 'author' ],
			'public'      => false,
			'show_ui'     => true,
			'has_archive' => false,
			'hierarchical' => false,
			'menu_icon'   => 'dashicons-phone',
			'rewrite'     => [ 'slug' => 'so-web' ],
		];

		register_post_type( 'so-web', $args );
	}

	public function add_menu() {
		if ( current_user_can( 'manage_options' ) ) {
			$page = add_submenu_page(
				'edit.php?post_type=so-web',
				__( 'Xem thông tin', 'ass' ),
				__( 'Xem thông tin', 'ass' ),
				'manage_options',
				'thong-tin-so-web',
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
			include ASS_DIR . 'templates/admin/so-web-views.php';
		} else {
			wp_die( 'Bạn không có quyền truy cập trang này' );
		}
	}

	public function enqueue() {
		// wp_enqueue_style( 'baocao', trailingslashit( ASS_URL ) . "assets/css/baocao.css", [], filemtime( trailingslashit( ASS_DIR ) . "assets/css/baocao.css" ) );
	}

	public static function get_data() {
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'chi_phi_don';
		$order   = isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : 'DESC';

		$args = [
			'post_type'      => 'so-web',
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
					'sdt' => get_the_title( $post_id ),
					'sdt_chamdinhdang'  => get_post_meta( $post_id, 'sdt_chamdinhdang', true ),
					'id_kho'  => get_post_meta( $post_id, 'id_kho', true ),
					'coc_sim'  => get_post_meta( $post_id, 'coc_sim', true ),
					'gia_ban_le'  => get_post_meta( $post_id, 'gia_ban_le', true ),
					'gia_dai_ly'  => get_post_meta( $post_id, 'gia_dai_ly', true ),
					'loai_sim'  => get_post_meta( $post_id, 'loai_sim', true ),
					'cam_ket'  => get_post_meta( $post_id, 'cam_ket', true ),
					'kenh_ban'  => get_post_meta( $post_id, 'kenh_ban', true ),
					'ngay_ban'  => get_post_meta( $post_id, 'ngay_ban', true ),
					'tinh_trang_ban'  => get_post_meta( $post_id, 'tinh_trang_ban', true ),
					'ghi_chu'  => get_post_meta( $post_id, 'ghi_chu', true ),
				];
			}
		}
		wp_reset_postdata();

		return $data_so_tmdt;
	}
}
