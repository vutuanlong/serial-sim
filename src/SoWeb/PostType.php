<?php

namespace ASS\SoWeb;

class PostType {
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
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

}
