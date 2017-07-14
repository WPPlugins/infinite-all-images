<?php
/**
 * Infinite All Images
 * 
 * @package    InfiniteAllImages
 * @subpackage InfiniteAllImages registered in the database
    Copyright (c) 2016- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
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

class InfiniteAllImagesRegist {

	/* ==================================================
	 * Settings register
	 * @since	1.0
	 */
	function register_settings(){

		$allusers = FALSE;
		$display = 20;
		$width = 100;
		$margin = 1;
		$sort = 'new';
		$exclude_id = '';
		$parent = TRUE;
		$loading_image = INFINITEALLIMAGES_PLUGIN_URL.'/img/ajax-loader.gif';
		$exif_text = '%camera%(%focal_length%,%aperture%,%shutter_speed%,%iso%)[%credit% %caption% %created_timestamp% %copyright% %title%]';
		if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && get_locale() === 'ja' ) { // Japanese Windows
			$character_code = 'CP932';
		} else {
			$character_code = 'UTF-8';
		}

		$user = wp_get_current_user();
		$userid = $user->ID;
		$wp_options_name = 'infinite_all_images'.'_'.$userid;

		if ( !get_option($wp_options_name) ) {
			if ( get_option('infinite_all_images') ) { // old settings
				$infinite_all_images_settings = get_option('infinite_all_images');
				if ( array_key_exists( "display", $infinite_all_images_settings ) ) {
					$display = $infinite_all_images_settings['display'];
				}
				if ( array_key_exists( "width", $infinite_all_images_settings ) ) {
					$width = $infinite_all_images_settings['width'];
				}
				if ( array_key_exists( "sort", $infinite_all_images_settings ) ) {
					$sort = $infinite_all_images_settings['sort'];
				}
				if ( array_key_exists( "exclude_id", $infinite_all_images_settings ) ) {
					$exclude_id = $infinite_all_images_settings['exclude_id'];
				}
				if ( array_key_exists( "parent", $infinite_all_images_settings ) ) {
					$parent = $infinite_all_images_settings['parent'];
				}
				if ( array_key_exists( "loading_image", $infinite_all_images_settings ) ) {
					$loading_image = $infinite_all_images_settings['loading_image'];
				}
				if ( array_key_exists( "exif_text", $infinite_all_images_settings ) ) {
					$exif_text = $infinite_all_images_settings['exif_text'];
				}
				if ( array_key_exists( "character_code", $infinite_all_images_settings ) ) {
					$character_code = $infinite_all_images_settings['character_code'];
				}
				delete_option( 'infinite_all_images' );
			}
		} else {
			$infinite_all_images_settings = get_option($wp_options_name);
			if ( array_key_exists( "allusers", $infinite_all_images_settings ) ) {
				$allusers = $infinite_all_images_settings['allusers'];
			}
			if ( array_key_exists( "display", $infinite_all_images_settings ) ) {
				$display = $infinite_all_images_settings['display'];
			}
			if ( array_key_exists( "width", $infinite_all_images_settings ) ) {
				$width = $infinite_all_images_settings['width'];
			}
			if ( array_key_exists( "margin", $infinite_all_images_settings ) ) {
				$margin = $infinite_all_images_settings['margin'];
			}
			if ( array_key_exists( "sort", $infinite_all_images_settings ) ) {
				$sort = $infinite_all_images_settings['sort'];
			}
			if ( array_key_exists( "exclude_id", $infinite_all_images_settings ) ) {
				$exclude_id = $infinite_all_images_settings['exclude_id'];
			}
			if ( array_key_exists( "parent", $infinite_all_images_settings ) ) {
				$parent = $infinite_all_images_settings['parent'];
			}
			if ( array_key_exists( "loading_image", $infinite_all_images_settings ) ) {
				$loading_image = $infinite_all_images_settings['loading_image'];
			}
			if ( array_key_exists( "exif_text", $infinite_all_images_settings ) ) {
				$exif_text = $infinite_all_images_settings['exif_text'];
			}
			if ( array_key_exists( "character_code", $infinite_all_images_settings ) ) {
				$character_code = $infinite_all_images_settings['character_code'];
			}
		}

		$infinite_all_images_tbl = array(
							'allusers' => $allusers,
							'display' => $display,
							'width' => $width,
							'margin' => $margin,
							'sort' => $sort,
							'exclude_id' => $exclude_id,
							'parent' => $parent,
							'loading_image' => $loading_image,
							'exif_text' => $exif_text,
							'character_code' => $character_code
							);
		update_option( $wp_options_name, $infinite_all_images_tbl );

	}

}

?>