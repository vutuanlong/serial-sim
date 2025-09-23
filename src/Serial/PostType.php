<?php

namespace ASS\Serial;

class PostType {
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_filter( 'rwmb_meta_boxes', [ $this, 'register_meta_boxes' ] );

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
