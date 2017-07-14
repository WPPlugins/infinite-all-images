<?php
/*
Plugin Name: Infinite All Images
Plugin URI: https://wordpress.org/plugins/infinite-all-images/
Version: 2.00
Description: All the images of the media library to display in masonry and Infinite scroll.
Author: Katsushi Kawamori
Author URI: http://riverforest-wp.info/
Text Domain: infinite-all-images
Domain Path: /languages
*/

/*  Copyright (c) 2016- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

	load_plugin_textdomain('infinite-all-images');
//	load_plugin_textdomain('infinite-all-images', false, basename( dirname( __FILE__ ) ) . '/languages' );

	define("INFINITEALLIMAGES_PLUGIN_BASE_FILE", plugin_basename(__FILE__));
	define("INFINITEALLIMAGES_PLUGIN_BASE_DIR", dirname(__FILE__));
	define("INFINITEALLIMAGES_PLUGIN_URL", plugins_url($path='infinite-all-images',$scheme=null));

	require_once( INFINITEALLIMAGES_PLUGIN_BASE_DIR . '/req/InfiniteAllImagesRegist.php' );
	$infiniteallimagesregist = new InfiniteAllImagesRegist();
	add_action('admin_init', array($infiniteallimagesregist, 'register_settings'));
	unset($infiniteallimagesregist);

	require_once( INFINITEALLIMAGES_PLUGIN_BASE_DIR . '/req/InfiniteAllImagesAdmin.php' );
	$infiniteallimagesadmin = new InfiniteAllImagesAdmin();
	add_action( 'admin_menu', array($infiniteallimagesadmin, 'plugin_menu'));
	add_action( 'admin_enqueue_scripts', array($infiniteallimagesadmin, 'load_custom_wp_admin_style') );
	add_filter( 'plugin_action_links', array($infiniteallimagesadmin, 'settings_link'), 10, 2 );
	add_filter( 'manage_media_columns', array($infiniteallimagesadmin, 'posts_columns_attachment_id'), 1);
	add_action( 'manage_media_custom_column', array($infiniteallimagesadmin, 'posts_custom_columns_attachment_id'), 1, 2);
	unset($infiniteallimagesadmin);

	include_once( INFINITEALLIMAGES_PLUGIN_BASE_DIR.'/inc/InfiniteAllImages.php' );
	$infiniteallimages = new InfiniteAllImages();
	add_action('wp_print_styles', array($infiniteallimages, 'load_styles'));
	add_shortcode( 'iai', array($infiniteallimages, 'infiniteallimages_func'));
	add_action( 'wp_enqueue_scripts', array($infiniteallimages, 'load_frontend_scripts' ) );
	add_action( 'wp_footer', array($infiniteallimages, 'load_localize_scripts_styles') );
	unset($infiniteallimages);

?>