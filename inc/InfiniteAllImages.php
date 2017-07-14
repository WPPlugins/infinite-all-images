<?php
/**
 * Infinite All Images
 * 
 * @package    InfiniteAllImages
 * @subpackage InfiniteAllImages Main Functions
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

class InfiniteAllImages {

	public $loading_image;
	public $width;
	public $margin;
	public $maxpage;

	/* ==================================================
	 * Main short code
	 * @param	array	$atts
	 * @return	array	$html
	 */
	function infiniteallimages_func( $atts, $html = NULL ) {

		$post = get_post(get_the_ID());
		$userid = $post->post_author;

		$wp_options_name = 'infinite_all_images'.'_'.$userid;
		$infiniteallimages_option = get_option($wp_options_name);

		extract(shortcode_atts(array(
			'display' => '',
			'width' => '',
			'margin' => '',
			'sort' => '',
			'exclude_id' => '',
			'parent' => '',
			'loading_image' => ''
		), $atts));

		$page = NULL;
		if (!empty($_GET['p_iai'])){
			$page = $_GET['p_iai'];			//pages
		}

		if ( empty($sort) ) { $sort = $infiniteallimages_option['sort']; }
		$sort_key = NULL;
		$sort_order = NULL;
		if ($sort === 'new') {
			$sort_key = 'post_date';
			$sort_order = 'DESC';
		} else if ($sort === 'old') {
			$sort_key = 'post_date';
			$sort_order = 'ASC';
		} else if ($sort === 'des') {
			$sort_key = 'post_title';
			$sort_order = 'DESC';
		} else if ($sort === 'asc') {
			$sort_key = 'post_title';
			$sort_order = 'ASC';
		} else {
			$sort_key = 'post_date';
			$sort_order = 'DESC';
		}

		if ( $infiniteallimages_option['allusers'] )  {
			$users = get_users();
			$postauthor = 'post_author IN(';
			foreach ( $users as $user ) {
				$postauthor .= $user->ID.',';
			}
			$postauthor = rtrim($postauthor, ',');
			$postauthor .= ')';
		} else {
			$postauthor = "post_author = '".$userid."'";
		}

		$mimepattern_count = 0;
		$postmimetype = NULL;
		$mimes = get_allowed_mime_types();
		foreach ( $mimes as $type => $mime ) {
			if (substr($mime, 0, 5) === 'image') {
				if ( $mimepattern_count == 0 ) {
					$postmimetype .= 'AND post_mime_type IN("'.$mime.'"';
				} else {
					$postmimetype .= ',"'.$mime.'"';
				}
				++ $mimepattern_count;
			}
		}
		$postmimetype .= ')';

		$notid = NULL;
		if ( empty($exclude_id) ) { $exclude_id = stripslashes($infiniteallimages_option['exclude_id']); }
		if ( !empty($exclude_id) ) {
			$notid = 'AND ID NOT IN ('.$exclude_id.')';
		}

		global $wpdb;
		$attachments = $wpdb->get_results("
						SELECT ID, post_title, post_date, post_parent
						FROM	$wpdb->posts
						WHERE	$postauthor
								$postmimetype
								$notid
								ORDER BY $sort_key $sort_order
						");

		$files = array();
		$files = $this->scan_media($attachments, stripslashes($infiniteallimages_option['exif_text']), $infiniteallimages_option['character_code']);
		unset($attachments);

		if ( empty($parent) ) { $parent = intval($infiniteallimages_option['parent']); }
		if ( empty($loading_image) ) { $loading_image = $infiniteallimages_option['loading_image']; }
		if ( empty($width) ) { $width = intval($infiniteallimages_option['width']); }
		if ( empty($margin) ) { $margin = intval($infiniteallimages_option['margin']); }
		if ( empty($display) ) { $display = intval($infiniteallimages_option['display']); }
		$maxpage = ceil(count($files) / $display);
		if(empty($page)){
			$page = 1;
		}

		$this->loading_image = $loading_image;
		$this->maxpage = $maxpage;
		$this->width = $width;
		$this->margin = $margin;

		$beginfiles = 0;
		$endfiles = 0;
		if( $page == $maxpage){
			$beginfiles = $display * ( $page - 1 );
			$endfiles = count($files) - 1;
		}else{
			$beginfiles = $display * ( $page - 1 );
			$endfiles = ( $display * $page ) - 1;
		}

		$linkfiles = NULL;
		$selectedfilename = NULL;
		if ($files) {
			for ( $i = $beginfiles; $i <= $endfiles; $i++ ) {
				$linkfile = $this->print_file($files[$i]['imgurl'], $files[$i]['title'], $files[$i]['thumburl'], $files[$i]['metadata'], $files[$i]['parent_id'], $parent);
				$linkfiles = $linkfiles.$linkfile;
			}
		}

		$linkpages = NULL;
		$linkpages = $this->print_pages($page, $maxpage);

		$linkfiles_begin = NULL;
		$linkfiles_end = NULL;
		$linkpages_begin = NULL;
		$linkpages_end = NULL;
		$sortlink_begin = NULL;
		$sortlink_end = NULL;
		$searchform_begin = NULL;
		$searchform_end = NULL;

		$linkfiles_begin = '<div id="infiniteallimages">';
		$linkfiles_end = '</div><div style="clear: both;"></div>';
		$linkpages_begin = '<div style="width: 100%; text-align: center;">';
		$linkpages_end = '</div>';

		$html .= $linkfiles_begin;
		$html .= $linkfiles;
		$html .= $linkfiles_end;

		$html .= $linkpages_begin;
		$html .= $linkpages;
		$html .= $linkpages_end;

		$html = apply_filters( 'post_infiniteallimages', $html );

		return $html;

	}

	/* ==================================================
	 * @param	array	$attachments
	 * @param	string	$exif_text_tag
	 * @param	string	$character_code
	 * @return	array	$files
	 * @since	1.0
	 */
	function scan_media($attachments, $exif_text_tag, $character_code){

		$attachment = NULL;
		$title = NULL;
		$filecount = 0;
		$files = array();
		if ($attachments) {
			foreach ( $attachments as $attachment ) {
				$title = $attachment->post_title;

				if ( function_exists('mb_language') && $character_code <> 'none' ) {
					$file_size = filesize(mb_convert_encoding(get_attached_file($attachment->ID), $character_code, "auto"));
				} else {
					$file_size = filesize(get_attached_file($attachment->ID));
				}
				$view_file_size = ' '.size_format($file_size);

				$datetime = $attachment->post_date;
				$view_datetime = ' '.$datetime;

				$exifdata = NULL;
				$exifdatas = array();
				$metadata = wp_get_attachment_metadata( $attachment->ID, FALSE );
				if ( $metadata['image_meta']['title'] ) {
					$exifdatas['title'] = $metadata['image_meta']['title'];
				}
				if ( $metadata['image_meta']['credit'] ) {
					$exifdatas['credit'] = $metadata['image_meta']['credit'];
				}
				if ( $metadata['image_meta']['camera'] ) {
					$exifdatas['camera'] = $metadata['image_meta']['camera'];
				}
				if ( $metadata['image_meta']['caption'] ) {
					$exifdatas['caption'] = $metadata['image_meta']['caption'];
				}
				$exif_ux_time = $metadata['image_meta']['created_timestamp'];
				if ( !empty($exif_ux_time) ) {
					$exifdatas['created_timestamp'] = date_i18n( "Y-m-d H:i:s", $exif_ux_time, FALSE );
				}
				if ( $metadata['image_meta']['copyright'] ) {
					$exifdatas['copyright'] = $metadata['image_meta']['copyright'];
				}
				if ( $metadata['image_meta']['aperture'] ) {
					$exifdatas['aperture'] = 'f/'.$metadata['image_meta']['aperture'];
				}
				if ( $metadata['image_meta']['shutter_speed'] ) {
					if ( $metadata['image_meta']['shutter_speed'] < 1 ) {
						$shutter = round( 1 / $metadata['image_meta']['shutter_speed'] );
						$exifdatas['shutter_speed'] = '1/'.$shutter.'sec';
					} else {
						$exifdatas['shutter_speed'] = $metadata['image_meta']['shutter_speed'].'sec';
					}
				}
				if ( $metadata['image_meta']['iso'] ) {
					$exifdatas['iso'] = 'ISO-'.$metadata['image_meta']['iso'];
				}
				if ( $metadata['image_meta']['focal_length'] ) {
					$exifdatas['focal_length'] = $metadata['image_meta']['focal_length'].'mm';
				}

				$exif_text = NULL;
				if ( $exifdatas ) {
					$exif_text = $exif_text_tag;
					foreach($exifdatas as $item => $exif) {
						$exif_text = str_replace('%'.$item.'%', $exif, $exif_text);
					}
					preg_match_all('/%(.*?)%/', $exif_text, $exif_text_per_match);
					foreach($exif_text_per_match as $key1) {
						foreach($key1 as $key2) {
							$exif_text = str_replace('%'.$key2.'%', '', $exif_text);
						}
					}
				}
				$metadata = $view_datetime.' '.$view_file_size.' '.$exif_text;

				$thumburl = NULL;
				$imgurl = NULL;
				$img_src = wp_get_attachment_image_src($attachment->ID, 'full', FALSE);
				$imgurl = $img_src[0];
				$thumb_src = wp_get_attachment_image_src($attachment->ID, 'thumbnail', FALSE);
				$thumburl = $thumb_src[0];
				$files[$filecount]['imgurl'] = $imgurl;
				$files[$filecount]['title'] = $title;
				$files[$filecount]['thumburl'] = $thumburl;
				$files[$filecount]['metadata'] = $metadata;
				$files[$filecount]['parent_id'] = $attachment->post_parent;
				++$filecount;
			}
		}

		return $files;

	}

	/* ==================================================
	 * @param	string	$imgurl
	 * @param	string	$title
	 * @param	string	$thumburl
	 * @param	string	$metadata
	 * @param	int		$parent_id
	 * @param	bool	$parent
	 * @return	string	$linkfile
	 * @since	1.0
	 */
	function print_file($imgurl, $title, $thumburl, $metadata, $parent_id, $parent) {

		$parent_title = NULL;
		if ( $parent && $parent_id > 0 ) {
			$parent_title = get_the_title($parent_id);
			$imglink = get_permalink($parent_id);
			$targetblank = ' target="_blank"';
		} else {
			$imglink = $imgurl;
			$targetblank = NULL;
		}

		$titles = $parent_title.' '.$title.' '.$metadata;

		$linkfile = NULL;
		if ( is_single() || is_page() ) {
			$linkfile = '<a href="'.$imglink.'" title="'.$titles.'"'.$targetblank.'><img src="'.$imgurl.'" alt="'.$title.'" title="'.$titles.'" class="infiniteallimagesitem"></a>';
		} else {
			$linkfile = '<a href="'.$imglink.'" title="'.$titles.'"'.$targetblank.'><img src="'.$imgurl.'" alt="'.$title.'" title="'.$titles.'"></a>';
		}

		return $linkfile;

	}

	/* ==================================================
	 * @param	int		$page
	 * @param	int		$maxpage
	 * @return	string	$linkpages
	 * @since	1.0
	 */
	function print_pages($page, $maxpage) {

		$query = get_permalink();
		$new_query = add_query_arg( array('p_iai' => $page+1), $query );

		$linkpages = NULL;

		if ( is_single() || is_page() ) {
			if( $page >= 1 && $maxpage > $page ){
				$linkpages = '<div class="infiniteallimages-nav"><a rel="next" href="'.$new_query.'"></a><span class="dashicons dashicons-arrow-down-alt"></span></div>';
			}
		}

		return $linkpages;

	}

	/* ==================================================
	* Load Script
	* @param	none
	* @since	2.00
	*/
	function load_frontend_scripts(){
		if ( is_single() || is_page() ) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-masonry');
			wp_enqueue_script('infinitescroll', INFINITEALLIMAGES_PLUGIN_URL.'/js/jquery.infinitescroll.min.js', null, '2.1.0');
		}
	}

	/* ==================================================
	* Load Localize Script and Style
	* @param	none
	* @since	2.00
	*/
	function load_localize_scripts_styles() {

		if ( ( is_single() || is_page() ) && !is_null($this->width) ) {
			wp_enqueue_script( 'infiniteallimages-jquery', INFINITEALLIMAGES_PLUGIN_URL.'/js/jquery.infiniteallimages.js',array('jquery'));
			$localize_iai_settings = array(
										'loading_image'	=> $this->loading_image,
										'maxpage' 		=> $this->maxpage
											);
			wp_localize_script( 'infiniteallimages-jquery', 'iai_settings', $localize_iai_settings );
			wp_enqueue_style( 'infiniteallimages',  INFINITEALLIMAGES_PLUGIN_URL.'/css/infiniteallimages.css' );
			$css = '.infiniteallimagesitem { width: '.$this->width.'px; } #infiniteallimages img{ margin: '.$this->margin.'px; }
';
			wp_add_inline_style( 'infiniteallimages', $css );
		} else {
			wp_enqueue_style( 'infiniteallimages',  INFINITEALLIMAGES_PLUGIN_URL.'/css/infiniteallimages.dummy.css' );
		}

	}

	/* ==================================================
	 * Load Dashicons
	 * @since	1.0
	 */
	function load_styles() {
		wp_enqueue_style('dashicons');
	}

}

?>