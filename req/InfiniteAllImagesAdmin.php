<?php
/**
 * Infinite All Images
 * 
 * @package    InfiniteAllImages
 * @subpackage InfiniteAllImages Management screen
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

class InfiniteAllImagesAdmin {

	/* ==================================================
	 * Add a "Settings" link to the plugins page
	 * @since	1.0
	 */
	function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty($this_plugin) ) {
			$this_plugin = INFINITEALLIMAGES_PLUGIN_BASE_FILE;
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="'.admin_url('options-general.php?page=InfiniteAllImages').'">'.__( 'Settings').'</a>';
		}
			return $links;
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function plugin_menu() {
		add_options_page( 'Infinite All Images Options', 'Infinite All Images', 'upload_files', 'InfiniteAllImages', array($this, 'plugin_options') );
	}

	/* ==================================================
	 * Add Css and Script
	 * @since	1.0
	 */
	function load_custom_wp_admin_style() {
		if ($this->is_my_plugin_screen()) {
			wp_enqueue_style( 'jquery-responsiveTabs', INFINITEALLIMAGES_PLUGIN_URL.'/css/responsive-tabs.css' );
			wp_enqueue_style( 'jquery-responsiveTabs-style', INFINITEALLIMAGES_PLUGIN_URL.'/css/style.css' );
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'jquery-responsiveTabs', INFINITEALLIMAGES_PLUGIN_URL.'/js/jquery.responsiveTabs.min.js' );
			wp_enqueue_script( 'infiniteallimages-admin-js', INFINITEALLIMAGES_PLUGIN_URL.'/js/jquery.infiniteallimages.admin.js', array('jquery') );
		}
	}

	/* ==================================================
	 * For only admin style
	 * @since	1.0
	 */
	function is_my_plugin_screen() {
		$screen = get_current_screen();
		if (is_object($screen) && $screen->id == 'settings_page_InfiniteAllImages') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/* ==================================================
	 * Settings page
	 * @since	1.0
	 */
	function plugin_options() {

		if ( !current_user_can( 'upload_files' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		if( !empty($_POST) ) {
			$settings_tabs = intval($_POST['infiniteallimages_admin_tabs']);
			$post_nonce_field = 'iai_tabs'.$settings_tabs;
			if ( isset($_POST[$post_nonce_field]) && $_POST[$post_nonce_field] ) {
				if ( check_admin_referer( 'iai_settings'.$settings_tabs, $post_nonce_field ) ) {
					$this->options_updated($settings_tabs);
				}
			}
		}

		$scriptname = admin_url('options-general.php?page=InfiniteAllImages');

		$user = wp_get_current_user();
		$userid = $user->ID;
		$wp_options_name = 'infinite_all_images'.'_'.$userid;
		$infiniteallimages_option = get_option($wp_options_name);

		?>

	<div class="wrap">
	<h2>Infinite All Images</h2>

	<div id="infiniteallimages-admin-tabs">
	  <ul>
	    <li><a href="#infiniteallimages-admin-tabs-1"><?php _e('How to use', 'infinite-all-images'); ?></a></li>
	    <li><a href="#infiniteallimages-admin-tabs-2"><?php _e('Settings'); ?></a></li>
		<li><a href="#infiniteallimages-admin-tabs-3"><?php _e('Donate to this plugin &#187;'); ?></a></li>
	<!--
		<li><a href="#infiniteallimages-admin-tabs-4">FAQ</a></li>
	 -->
	  </ul>
	  <div id="infiniteallimages-admin-tabs-1">
		<div class="wrap">

			<h2><?php _e('How to use', 'infinite-all-images'); ?></h2>
			<div style="padding:10px;"><?php _e('Please add new Page. Please write a short code in the text field of the Page. Please go in Text mode this task.', 'infinite-all-images'); ?></div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<h3><?php _e('short code', 'infinite-all-images'); ?></h3>

				<div style="padding: 5px 20px; font-weight: bold;"><?php _e('Example', 'infinite-all-images'); ?></div>
				<div style="padding: 5px 35px;"><code>[iai]</code></div>
				<div style="padding: 5px 35px;"><code>[iai display=25 width=150 margin=2 sort="old" parent=1 loading_image="http://localhost.localdomain/wp-content/uploads/loader.gif" exclude_id="123, 213, 312"]</code></div>

				<div style="padding: 5px 20px; font-weight: bold;"><?php _e('Description of each attribute', 'infinite-all-images'); ?></div>

				<div style="padding: 5px 35px;"><?php _e('Number of items per page:'); ?><code>display</code></div>
				<div style="padding: 5px 35px;"><?php _e('Width of one column of the image:', 'infinite-all-images'); ?><code>width</code>(px)</div>
				<div style="padding: 5px 35px;"><?php _e('Margin between images:', 'infinite-all-images'); ?><code>margin</code>(px)</div>
				<div style="padding: 5px 35px;"><?php _e('Type of Sort:', 'infinite-all-images'); ?><code>sort</code></div>
				<div style="padding: 5px 35px;"><?php _e('If the image is attached to the post, to link to the post URL:', 'infinite-all-images'); ?><code>parent=1</code></div>
				<div style="padding: 5px 35px;"><?php _e('loading_image:', 'infinite-all-images'); ?><code>loading_image</code>(url)</div>

				<div style="padding: 5px 35px;"><?php _e('Specifies a comma-separated list to exclusion media ID:', 'infinite-all-images'); ?><code>exclude_id</code></div>
				<?php
				$settings_html = '<a href="'.$scriptname.'#infiniteallimages-admin-tabs-2" style="text-decoration: none; word-break: break-all;">'.__('Settings', 'infinite-all-images').'</a>';
				?>
				<div style="padding: 5px 20px; font-weight: bold;"><?php echo sprintf(__('Attribute value of short codes can also be specified in the %1$s. Attribute value of the short code takes precedence.', 'infinite-all-images'), $settings_html); ?></div>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<h3><?php _e('Filter', 'infinite-all-images') ?></h3>
				<?php
					if ( is_multisite() ) {
						$boxersandswipers_install_url = network_admin_url('plugin-install.php?tab=plugin-information&plugin=Boxers+and+Swipers');
					} else {
						$boxersandswipers_install_url = admin_url('plugin-install.php?tab=plugin-information&plugin=Boxers+and+Swipers');
					}
					$boxersandswipers_install_html = '<a href="'.$boxersandswipers_install_url.'" target="_blank" style="text-decoration: none; word-break: break-all;">Boxers and Swipers</a>';
				?>
				<div style="padding: 5px 20px; font-weight: bold;"><?php echo sprintf(__('It is possible to work with %1$s. Please install.', 'infinite-all-images'), $boxersandswipers_install_html); ?></div>
				<div style="padding: 5px 20px; font-weight: bold;"><?php _e('In addition, offer the following filters. This filter passes the html that is generated.', 'infinite-all-images'); ?></div>
				<div style="display:block; padding: 5px 35px;">
				<code>post_infiniteallimages</code>
				</div>
			</div>

		</div>
	  </div>

	  <div id="infiniteallimages-admin-tabs-2">
		<div class="wrap">

			<form method="post" action="<?php echo $scriptname.'#infiniteallimages-admin-tabs-2'; ?>">
			<?php wp_nonce_field('iai_settings2', 'iai_tabs2'); ?>

			<h2><?php _e('Settings'); ?></h2>	

			<div class="submit">
				<?php submit_button( __('Save Changes'), 'large', 'Submit', FALSE ); ?>
				<?php submit_button( __('Default'), 'large', 'Default', FALSE ); ?>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">

				<div style="display: block; padding:5px 5px;">
					<h3><?php _e('Display', 'infinite-all-images'); ?></h3>
					<?php
					if ( current_user_can( 'manage_options' ) )  {
					?>
					<div style="display: block; padding:5px 20px;">
						<?php _e('Displays images of all users:', 'infinite-all-images'); ?>
						<input type="checkbox" name="infiniteallimages_allusers" value="1" <?php checked('1', $infiniteallimages_option['allusers']); ?> />
					</div>
					<?php
					}
					?>
					<div style="display: block; padding:5px 20px;">
					<?php _e('Number of items per page:'); ?><input type="number" step="1" min="1" max="99" maxlength="2" class="screen-per-page" name="infiniteallimages_display" value="<?php echo intval($infiniteallimages_option['display']); ?>" size="3" />
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php _e('Width of one column of the image:', 'infinite-all-images'); ?><input type="number" step="1" min="10" max="999" maxlength="3" class="screen-per-page" name="infiniteallimages_width" value="<?php echo intval($infiniteallimages_option['width']); ?>" size="3" />px
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php _e('Margin between images:', 'infinite-all-images'); ?><input type="number" step="1" min="1" max="99" maxlength="2" class="screen-per-page" name="infiniteallimages_margin" value="<?php echo intval($infiniteallimages_option['margin']); ?>" size="3" />px
					</div>
					<div style="display: block; padding:5px 20px;">
					<?php _e('Type of Sort:', 'infinite-all-images'); ?>
					<select id="infiniteallimages_sort" name="infiniteallimages_sort">
						<option <?php if ('new' == $infiniteallimages_option['sort'])echo 'selected="selected"'; ?>>new</option>
						<option <?php if ('old' == $infiniteallimages_option['sort'])echo 'selected="selected"'; ?>>old</option>
						<option <?php if ('des' == $infiniteallimages_option['sort'])echo 'selected="selected"'; ?>>des</option>
						<option <?php if ('asc' == $infiniteallimages_option['sort'])echo 'selected="selected"'; ?>>asc</option>
					</select>
					</div>
					<div style="display: block; padding:5px 20px;">
						<?php _e('If the image is attached to the post, to link to the post URL:', 'infinite-all-images'); ?>
						<input type="checkbox" name="infiniteallimages_parent" value="1" <?php checked('1', $infiniteallimages_option['parent']); ?> />
					</div>
					<div style="display: block; padding:5px 20px;">
						<?php _e('loading_image:', 'infinite-all-images'); ?>
						<input type="text" style="width: 80%;"name="infiniteallimages_loading_image" value="<?php echo $infiniteallimages_option['loading_image'] ?>" />
					</div>
				</div>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<div style="display: block; padding:5px 5px;">
					<h3><?php _e('Exclude', 'infinite-all-images'); ?> ID</h3>
					<div style="display: block; padding:5px 20px;">
					<?php _e('Specifies a comma-separated list to exclusion media ID:', 'infinite-all-images'); ?>
					<textarea name="infiniteallimages_exclude_id" style="width: 100%;"><?php echo stripslashes($infiniteallimages_option['exclude_id']); ?></textarea>
						<div>
						<?php
						$medialibrary_html = '<a href="'.admin_url( 'upload.php').'" target="_blank" style="text-decoration: none; word-break: break-all;">'.__('Media Library').'</a>';
						echo sprintf(__('When you activate this plugin, will be displayed ID is in the column of the %1$s','infinite-all-images'), $medialibrary_html);
						?>
						</div>
					</div>
				</div>
			</div>

			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<div style="display: block; padding:5px 5px;">
					<h3>Exif <?php _e('Tags'); ?></h3>
					<div style="display: block; padding:5px 20px;">
					<?php _e('Specifies tag for Exif View:', 'infinite-all-images'); ?>
					<textarea name="infiniteallimages_exif_text" style="width: 100%;"><?php echo stripslashes($infiniteallimages_option['exif_text']); ?></textarea>
						<div>
						<a href="https://codex.wordpress.org/Function_Reference/wp_read_image_metadata#Return%20Values" target="_blank" style="text-decoration: none; word-break: break-all;"><?php _e('For Exif tags, please read here.', 'infinite-all-images'); ?></a>
						</div>
					</div>
				</div>
			</div>

			<?php
			if ( function_exists('mb_check_encoding') ) {
			?>
			<div style="width: 100%; height: 100%; margin: 5px; padding: 5px; border: #CCC 2px solid;">
				<h3><?php _e('Character Encodings for Server', 'infinite-all-images'); ?></h3>
				<div style="display: block; padding:5px 20px;">
				<?php _e('It may receive an error if you are using a multi-byte name to the file or directory name. In that case, please change.', 'infinite-all-images');
				$characterencodings_none_html = '<a href="'.__('https://en.wikipedia.org/wiki/Variable-width_encoding', 'infinite-all-images').'" target="_blank" style="text-decoration: none; word-break: break-all;">'.__('variable-width encoding', 'infinite-all-images').'</a>';
				echo sprintf(__('If you do not use the filename or directory name of %1$s, please choose "%2$s".','infinite-all-images'), $characterencodings_none_html, '<font color="red">none</font>');
				 ?>
				</div>
				<div style="display: block; padding:5px 20px;">
				<select name="infiniteallimages_character_code" style="width: 210px">
				<?php
				if ( 'none' === $infiniteallimages_option['character_code'] ) {
					?>
					<option value="none" selected>none</option>
					<?php
				} else {
					?>
					<option value="none">none</option>
					<?php
				}
				foreach (mb_list_encodings() as $chrcode) {
					if ( $chrcode <> 'pass' && $chrcode <> 'auto' ) {
						if ( $chrcode === $infiniteallimages_option['character_code'] ) {
							?>
							<option value="<?php echo $chrcode; ?>" selected><?php echo $chrcode; ?></option>
							<?php
						} else {
							?>
							<option value="<?php echo $chrcode; ?>"><?php echo $chrcode; ?></option>
							<?php
						}
					}
				}
				?>
				</select>
				</div>
				<div style="clear: both;"></div>
			</div>
			<?php
			}
			?>

			<input type="hidden" name="infiniteallimages_admin_tabs" value="2" />
			<?php submit_button( __('Save Changes'), 'large', 'Submit', TRUE ); ?>

			</form>

		</div>
	  </div>

	  <div id="infiniteallimages-admin-tabs-3">
		<div class="wrap">
			<?php
			$plugin_datas = get_file_data( INFINITEALLIMAGES_PLUGIN_BASE_DIR.'/infiniteallimages.php', array('version' => 'Version') );
			$plugin_version = __('Version:').' '.$plugin_datas['version'];
			?>
			<h4 style="margin: 5px; padding: 5px;">
			<?php echo $plugin_version; ?> |
			<a style="text-decoration: none;" href="https://wordpress.org/support/plugin/infinite-all-images" target="_blank"><?php _e('Support Forums') ?></a> |
			<a style="text-decoration: none;" href="https://wordpress.org/support/view/plugin-reviews/infinite-all-images" target="_blank"><?php _e('Reviews', 'infinite-all-images') ?></a>
			</h4>
			<div style="width: 250px; height: 170px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
			<h3><?php _e('Please make a donation if you like my work or would like to further the development of this plugin.', 'infinite-all-images'); ?></h3>
			<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
	<a style="margin: 5px; padding: 5px;" href='https://pledgie.com/campaigns/28307' target="_blank"><img alt='Click here to lend your support to: Various Plugins for WordPress and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/28307.png?skin_name=chrome' border='0' ></a>
			</div>
		</div>
	  </div>

	<!--
	  <div id="infiniteallimages-admin-tabs-4">
		<div class="wrap">
		<h2>FAQ</h2>

		</div>
	  </div>
	-->

	</div>
	</div>
	<?php

	}

	/* ==================================================
	 * Update wp_options table.
	 * @param	string	$tabs
	 * @since	1.0
	 */
	function options_updated($tabs){

		$user = wp_get_current_user();
		$userid = $user->ID;
		$wp_options_name = 'infinite_all_images'.'_'.$userid;

		$loading_image = INFINITEALLIMAGES_PLUGIN_URL.'/img/ajax-loader.gif';
		$exif_text = '%camera%(%focal_length%,%aperture%,%shutter_speed%,%iso%)[%credit% %caption% %created_timestamp% %copyright% %title%]';
		if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && get_locale() === 'ja' ) { // Japanese Windows
			$character_code = 'CP932';
		} else {
			$character_code = 'UTF-8';
		}
		$infinite_all_images_reset_tbl = array(
								'allusers' => FALSE,
								'display' => 20,
								'width' => 100,
								'margin' => 1,
								'sort' => 'new',
								'exclude_id' => '',
								'parent' => TRUE,
								'loading_image' => $loading_image,
								'exif_text' => $exif_text,
								'character_code' => $character_code
					);

		switch ($tabs) {
			case 1:
				break;
			case 2:
				if ( !empty($_POST['Default']) ) {
					update_option( $wp_options_name, $infinite_all_images_reset_tbl );
					echo '<div class="notice notice-success is-dismissible"><ul><li>'.__('Settings').' --> '.__('Default').' --> '.__('Changes saved.').'</li></ul></div>';
				} else {
					if ( !empty($_POST['infiniteallimages_allusers']) ) {
						$infiniteallimages_allusers = intval($_POST['infiniteallimages_allusers']);
					} else {
						$infiniteallimages_allusers = FALSE;
					}
					if ( !empty($_POST['infiniteallimages_parent']) ) {
						$infiniteallimages_parent = intval($_POST['infiniteallimages_parent']);
					} else {
						$infiniteallimages_parent = FALSE;
					}
					$infinite_all_images_tbl = array(
									'allusers' => $infiniteallimages_allusers,
									'display' => intval($_POST['infiniteallimages_display']),
									'width' => intval($_POST['infiniteallimages_width']),
									'margin' => intval($_POST['infiniteallimages_margin']),
									'sort' => $_POST['infiniteallimages_sort'],
									'exclude_id' => $_POST['infiniteallimages_exclude_id'],
									'parent' => $infiniteallimages_parent,
									'loading_image' => $_POST['infiniteallimages_loading_image'],
									'exif_text' => $_POST['infiniteallimages_exif_text'],
									'character_code' => $_POST['infiniteallimages_character_code']
								);
					update_option( $wp_options_name, $infinite_all_images_tbl );
					echo '<div class="notice notice-success is-dismissible"><ul><li>'.__('Settings').' --> '.__('Changes saved.').'</li></ul></div>';
				}
				break;
		}

		return;

	}

	/* ==================================================
	 * View ID for Media Library
	 * https://gist.github.com/kachi/2208236#file-gistfile1-aw
	 * @since	1.0
	 */
	function posts_columns_attachment_id($defaults){
		$defaults['wps_post_attachments_id'] = 'ID';
		return $defaults;
	}

	function posts_custom_columns_attachment_id($column_name, $id){
		if($column_name === 'wps_post_attachments_id'){
			echo $id;
		}
	}

}

?>