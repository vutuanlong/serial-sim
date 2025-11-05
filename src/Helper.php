<?php

namespace ASS;

class Helper {
	public function init() {
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

	public static function get_serial_sim() {
		return [
			"Viettel",
			"Vinaphone",
		];
	}

	public static function dinh_dang_sim() {
		return [
			'Sim dễ nhớ',
			'Sim tam hoa',
			'Sim lặp kép',
			'Sim lộc phát',
			'Sim thần tài',
			'Sim ông địa',
			'Sim đảo đơn',
			'Sim năm sinh',
			'Sim sảnh tiến',
			'Sim tam hoa giữa',
			'Sim tứ quý giữa',
			'Sim số đối',
			'SIM SỐ VIP',
			'SIM GIÁ RẺ',
		];
	}

	public static function tinh_trang_ban_hang() {
		return [
			'Trong kho',
			'Chờ đăng',
			'Đang bán',
			'Đã bán',
			'Đã đăng ký',
			'Hoàn',
		];
	}
	public static function loai_sim() {
		return [
			'Trả trước',
			'Trả sau',
		];
	}
}
