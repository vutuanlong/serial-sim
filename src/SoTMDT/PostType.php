<?php

namespace ASS\SoTMDT;

class PostType {
	public function init() {
		add_action( 'init', [ $this, 'register_post_type' ] );
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

}
