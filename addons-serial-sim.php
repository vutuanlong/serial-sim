<?php
/**
 * Plugin Name: Plugin quản lý sim số
 * Plugin URI:  vsim.com.vn
 * Description: Plugin quản lý sim số
 * Version:     1.0.0
 * Author:      vsim.com.vn
 * Author URI:  vsim.com.vn
 */

namespace AML;

// Prevent loading this file directly.
defined( 'ABSPATH' ) || die;

require 'vendor/autoload.php';

define( 'ASS_URL', plugin_dir_url( __FILE__ ) );
define( 'ASS_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASS_VER', '1.0.0' );

load_plugin_textdomain( 'ass', false, plugin_basename( ASS_DIR ) . '/languages' );
