<?php
/*
Plugin Name: Popup Modal
Plugin URI: https://wordpress.org/plugins/popup-modal/
Description: Easily create responsive WYSIWYG modals that popup at your desired frequency and optionally print with a single click.
Version: 2.2.0
Author: Tim Eckel
Author URI: https://www.dogblocker.com
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: popup-modal
*/

/*
	Copyright 2023  Tim Eckel  (email : eckel.tim@gmail.com)
	Popup Modal is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	any later version.

	Popup Modal is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Popup Modal; if not, see https://www.gnu.org/licenses/gpl-3.0.html
*/

if ( !defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'TECKEL_POPUP_MODAL_URL' ) ) {
	if ( !function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	$plugin_data = get_plugin_data( __FILE__ );
	define( 'TECKEL_POPUP_MODAL_URL', plugin_dir_url( __FILE__ ) );
	define( 'TECKEL_POPUP_MODAL_PATH',  plugin_dir_path( __FILE__ ) );
	define( 'TECKEL_POPUP_MODAL_VERSION', $plugin_data['Version'] );
	add_action( 'init', function(){ new Teckel_Popup_Modal();}, 0 );
}

class Teckel_Popup_Modal {
	const POST_TYPE =  'popup_modal';
	const META_DISABLE = 'popup_disable';
	const META_FREQ = 'popup_freq';
	const META_EXPIRES = 'popup_expires';
	const META_PAGES = 'popup_pages';
	const META_CLICK = 'popup_click';
	const META_LOCATION = 'popup_location';
	const META_WIDTH = 'popup_width';
	const META_TITLE = 'popup_title';
	const META_COLOR = 'popup_color';
	const META_BGCOLOR = 'popup_bgcolor';
	const META_BUTTONS = 'popup_buttons';

	public function __construct() {
		$this->register_ctp();
		add_action( 'save_post_'.self::POST_TYPE, array( $this, 'post_save' ) );
		add_filter( 'manage_edit-'.self::POST_TYPE.'_columns', array( $this, 'popup_modal_columns' ) ) ;
		add_action( 'manage_'.self::POST_TYPE.'_posts_custom_column', array( $this, 'popup_modal_columns_data' ) , 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'include_popup' ) );
	}

	public function include_popup() {
		$popups = get_posts( array( 'post_type' => self::POST_TYPE ) );
		$allvars = array();
		foreach ($popups as $popup) {
			$vars = array();
			$vars['disable']	= get_post_meta( $popup->ID, self::META_DISABLE, true );
			$vars['freq']		= get_post_meta( $popup->ID, self::META_FREQ, true );
			$vars['expires']	= get_post_meta( $popup->ID, self::META_EXPIRES, true );
			$vars['pages']		= get_post_meta( $popup->ID, self::META_PAGES, true );
			$vars['click']		= get_post_meta( $popup->ID, self::META_CLICK, true );
			$vars['location']	= get_post_meta( $popup->ID, self::META_LOCATION, true );
			$vars['width']		= get_post_meta( $popup->ID, self::META_WIDTH, true );
			$vars['title']		= get_post_meta( $popup->ID, self::META_TITLE, true );
			$vars['color']		= get_post_meta( $popup->ID, self::META_COLOR, true );
			$vars['bgcolor']	= get_post_meta( $popup->ID, self::META_BGCOLOR, true );
			$vars['buttons']	= get_post_meta( $popup->ID, self::META_BUTTONS, true );
			$vars['id']			= $popup->ID;
			$vars['slug']		= $popup->post_name;
			$vars['poptitle']	= $popup->post_title;
			//$vars['body']		= wpautop($popup->post_content);
			//$vars['body']		= do_shortcode( wpautop( $popup->post_content ) );
			$vars['body']		= apply_filters( 'the_content', $popup->post_content );

			// Check expires date and see if the popup should occur
			if ( $vars['disable']!='yes' ) {
				if ( empty( $vars['expires'] ) ) {
					$allvars[] = $vars;
				} else {
					$dateParts = explode( '-', $vars['expires'] );
					if ( count( $dateParts ) == 3 ) {
						$expires = mktime( 23, 59, 59, $dateParts[0], $dateParts[1], $dateParts[2] );
						if ( time() <= $expires) {
							$allvars[] = $vars;
						}
					} else {
						// Expires date malformed, don't display popup
					}
				}
			}
		}
		if ( count( $allvars ) > 0 ) {
			wp_register_script( 'popup_modal', TECKEL_POPUP_MODAL_URL . 'popup-modal.js', 'jQuery', TECKEL_POPUP_MODAL_VERSION, true );
			wp_enqueue_script( 'popup_modal' );
			wp_enqueue_style( 'popup_modal', TECKEL_POPUP_MODAL_URL . 'popup-modal.css'  );
			wp_localize_script( 'popup_modal', 'popup_modal_data', $allvars );
			add_action( 'wp_footer' , array( $this , 'include_footer_template' ) );
		}
	}

	public function include_footer_template() {
		require_once ( TECKEL_POPUP_MODAL_PATH . 'template.php' );
	}

	private function register_ctp() {
		$labels = array(
			'name'				=> _x( 'Popup Modal', 'Post Type General Name', 'popup-modal' ),
			'singular_name'	=> _x( 'Popup', 'Post Type Singular Name', 'popup-modal' ),
			'menu_name'		=> __( 'Popup Modal', 'popup-modal' ),
			'all_items'			=> __( 'All Popups', 'popup-modal' ),
			'view_item'			=> __( 'View Popup', 'popup-modal' ),
			'add_new_item'	=> __( 'Add New Popup', 'popup-modal' ),
			'edit_item'			=> __( 'Edit Popup', 'popup-modal' ),
			'update_item'		=> __( 'Update Popup', 'popup-modal' ),
			'search_items'		=> __( 'Search Popup', 'popup-modal' ),
		);
		$args = array(
			'label'							=> self::POST_TYPE,
			'description'				=> __( 'Popup Modal', 'popup-modal' ),
			'labels'						=> $labels,
			'supports'					=> array( 'title', 'editor', 'revisions' ),
			'taxonomies'				=> array(),
			'hierarchical'				=> false,
			'public'						=> true,
			'show_ui'					=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'	=> false,
			'show_in_admin_bar'		=> false,
			'menu_position'			=> 25,
			'menu_icon'					=> 'dashicons-info',
			'can_export'				=> false,
			'has_archive'				=> false,
			'exclude_from_search'	=> true,
			'publicly_queryable'		=> true,
			'rewrite'						=> false,
			'capability_type'			=> 'post',
			'register_meta_box_cb'	=> array( $this, 'add_metabox' ),
		);
		register_post_type( self::POST_TYPE, $args );
	}

	public function popup_modal_columns( $columns ) {
		unset ($columns['date']);
		$columns += array(
			self::META_DISABLE => __( 'Active', 'popup-modal' ),
			self::META_FREQ => __( 'Freq', 'popup-modal' ),
			self::META_EXPIRES => __( 'Expires', 'popup-modal' ),
			self::META_PAGES => __( 'View On', 'popup-modal' ),
			self::META_CLICK => __( 'On Click', 'popup-modal' ),
			self::META_WIDTH => __( 'Width', 'popup-modal' ),
			'date' => __( 'Date', 'popup-modal' )
		);
		return $columns;
	}

	public function popup_modal_columns_data( $column, $post_id ) {
		global $post;
		if ( $column == self::META_DISABLE ) {
			$meta = get_post_meta( $post_id, self::META_DISABLE, true );
			if ( $meta!='yes' ) echo('&#10004;');
			else echo('&#10006;');
		} else if ( $column == self::META_FREQ ) {
			$meta = floatval( get_post_meta( $post_id, self::META_FREQ, true ) );
			if ( $meta>8760 ) _e( 'Once', 'popup-modal' );
			else if ( $meta==8760 ) _e( 'Yearly', 'popup-modal' );
			else if ( $meta==720 ) _e( 'Monthly', 'popup-modal' );
			else if ( $meta==168 ) _e( 'Weekly', 'popup-modal' );
			else if ( $meta==24 ) _e( 'Daily', 'popup-modal' );
			else if ( $meta==1 ) _e( 'Hourly', 'popup-modal' );
			else if ( $meta==0 ) _e( 'Session', 'popup-modal' );
			else if ( $meta==0.5 ) _e( '30 mins', 'popup-modal' );
			else if ( $meta==0.25 ) _e( '15 mins', 'popup-modal' );
			else if ( $meta==0.16666 ) _e( '10 mins', 'popup-modal' );
			else if ( $meta==0.08333 ) _e( '5 mins', 'popup-modal' );
			else if ( $meta==0.01666 ) _e( '1 min', 'popup-modal' );
			else if ( $meta==-1 ) _e( 'Always', 'popup-modal' );
			else {
				echo $meta;
				_e( ' Hours', 'popup-modal' );
			}
		} else if ( $column == self::META_EXPIRES ) {
			$meta = get_post_meta( $post_id, self::META_EXPIRES, true );
			if ( empty( $meta ) ) _e( 'Never', 'popup-modal' );
			else echo $meta;
		} else if ( $column == self::META_PAGES ) {
			$meta = get_post_meta( $post_id, self::META_PAGES, true );
			if ( $meta=='all' ) _e( 'All', 'popup-modal' );
			else if ( $meta=='front' ) _e( 'Front', 'popup-modal' );
			else if ( $meta=='interior' ) _e( 'Interior', 'popup-modal' );
		} else if ( $column == self::META_CLICK ) {
			$meta = get_post_meta( $post_id, self::META_CLICK, true );
			if ( $meta=='close' ) _e( 'Close', 'popup-modal' );
			else if ( $meta=='print' ) _e( 'Print', 'popup-modal' );
		} else if ( $column == self::META_WIDTH ) {
			$meta = get_post_meta( $post_id, self::META_WIDTH, true );
			echo $meta . 'px';
		}
	}

	public function post_save( $post_id ) {
		// Checks exits script depending on save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ self::POST_TYPE . '_nonce' ] ) && wp_verify_nonce( $_POST[ self::POST_TYPE . '_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
		if ( $is_autosave || $is_revision || !$is_valid_nonce) return;

		// Get previous mega values
		$stored_meta = get_post_meta( $post_id );

		// Sanitize the meta data
		$disable = sanitize_text_field( @$_POST[self::META_DISABLE] );
		$freq = sanitize_text_field( @$_POST[self::META_FREQ] );
		$expires = sanitize_text_field( @$_POST[self::META_EXPIRES] );
		$pages = sanitize_text_field( @$_POST[self::META_PAGES] );
		$click = sanitize_text_field( @$_POST[self::META_CLICK] );
		$location = sanitize_text_field( @$_POST[self::META_LOCATION] );
		$width = sanitize_text_field( @$_POST[self::META_WIDTH] );
		$title = sanitize_text_field( @$_POST[self::META_TITLE] );
		$color = strtolower ( sanitize_hex_color( @$_POST[self::META_COLOR] ) );
		$bgcolor = strtolower( sanitize_hex_color( @$_POST[self::META_BGCOLOR] ) );
		$buttons = sanitize_text_field( @$_POST[self::META_BUTTONS] );

		// Validate fields
		$width = min( max( intval( empty( $width )?800:$width ), 100 ), 2000 );
		if ( empty($color) ) $color = '#000000';
		if ( empty($bgcolor) ) $bgcolor = '#ffffff';

		// Save mega data
		update_post_meta( $post_id, self::META_DISABLE, $disable ); // Allow an empty value
		if ( is_numeric($freq) && $freq >= -1 && $freq <= 876000 ) update_post_meta( $post_id, self::META_FREQ, $freq );
		update_post_meta( $post_id, self::META_EXPIRES, $expires ); // Allow an empty value
		if ( !empty($pages) ) update_post_meta( $post_id, self::META_PAGES, $pages );
		if ( !empty($click) ) update_post_meta( $post_id, self::META_CLICK, $click );
		if ( !empty($location) ) update_post_meta( $post_id, self::META_LOCATION, $location );
		if ( !empty($width) ) update_post_meta( $post_id, self::META_WIDTH, $width );
		if ( !empty($title) ) update_post_meta( $post_id, self::META_TITLE, $title );
		if ( !empty($color) ) update_post_meta( $post_id, self::META_COLOR, $color );
		if ( !empty($bgcolor) ) update_post_meta( $post_id, self::META_BGCOLOR, $bgcolor );
		if ( !empty($buttons) ) update_post_meta( $post_id, self::META_BUTTONS, $buttons );
		
		// If frequency changed, remove cookie (for admin user) so it shows at the new frequency
		if ( $freq != @$stored_meta[self::META_FREQ][0] ) {
			$hostnameParts = explode( '.', $_SERVER['HTTP_HOST']);
			$secondLevelDomain = '.'.implode( '.', array_slice( $hostnameParts, -2 ) );
			unset( $_COOKIE['popup-modal-' . $post_id] );
			setcookie( 'popup-modal-' . $post_id, '', 1, '/', $secondLevelDomain );
		}
	}

	public function add_metabox() {
		add_meta_box(
			'popup_metabox',
			__( 'Popup Options', 'popup-modal' ),
			array($this, 'generate_metabox' ),
			self::POST_TYPE,
			'normal', // normal, side, advanced
			'high' // default, high, low
		);
	}

	public function generate_metabox( $post ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'jquery-style', TECKEL_POPUP_MODAL_URL . 'jquery-ui.css' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'popup_modal', TECKEL_POPUP_MODAL_URL . 'popup-modal.css'  );
		wp_enqueue_script( 'popup_modal', TECKEL_POPUP_MODAL_URL . 'popup-modal.js' );

		$popups = get_posts( array( 'post_type' => self::POST_TYPE ) );

		$stored_meta = get_post_meta( $post->ID );
		wp_nonce_field( plugin_basename(__FILE__), self::POST_TYPE . '_nonce' );

		// Get the meta values
		if ( isset ( $stored_meta[self::META_DISABLE] ) ) $popup_disable = $stored_meta[self::META_DISABLE][0]; else $popup_disable = '';
		if ( isset ( $stored_meta[self::META_FREQ] ) ) $popup_freq = floatval( $stored_meta[self::META_FREQ][0] );
		if ( isset ( $stored_meta[self::META_EXPIRES] ) ) $popup_expires = $stored_meta[self::META_EXPIRES][0]; else $popup_expires = '';
		if ( isset ( $stored_meta[self::META_PAGES] ) ) $popup_pages = $stored_meta[self::META_PAGES][0];
		if ( isset ( $stored_meta[self::META_CLICK] ) ) $popup_click = $stored_meta[self::META_CLICK][0];
		if ( isset ( $stored_meta[self::META_WIDTH] ) ) $popup_width = intval( $stored_meta[self::META_WIDTH][0] );
		if ( isset ( $stored_meta[self::META_TITLE] ) ) $popup_title = $stored_meta[self::META_TITLE][0];
		if ( isset ( $stored_meta[self::META_COLOR] ) ) $popup_color = $stored_meta[self::META_COLOR][0];
		if ( isset ( $stored_meta[self::META_BGCOLOR] ) ) $popup_bgcolor = $stored_meta[self::META_BGCOLOR][0];
		if ( isset ( $stored_meta[self::META_LOCATION] ) ) $popup_location = $stored_meta[self::META_LOCATION][0];
		if ( isset ( $stored_meta[self::META_BUTTONS] ) ) $popup_buttons = $stored_meta[self::META_BUTTONS][0];

		require_once ( TECKEL_POPUP_MODAL_PATH . 'template.php' );
?>
<table class="metabox-prefs">
	<tr class="megabox-prefs-disable">
		<td><label><?php _e( 'Disable popup', 'popup-modal' ); ?>:</label></td>
		<td>
			<input id="popup_disable" name="popup_disable" value="yes"<?php echo ($popup_disable=='yes' ? ' checked' : ''); ?> type="checkbox">
		</td>
	</tr>
	<tr class="megabox-prefs-freq metabox-options">
		<td><label><?php _e( 'Frequency', 'popup-modal' ); ?>:</label></td>
		<td>
			<select id="popup_freq" name="popup_freq" class="widefat">
				<option value="0"<?php echo ($popup_freq==0 ? ' selected' : ''); ?>><?php _e( 'Once a session', 'popup-modal' ); ?></option>
				<option value="-1"<?php echo ($popup_freq==-1 ? ' selected' : ''); ?>><?php _e( 'Every refresh (WARNING!)', 'popup-modal' ); ?></option>
				<option value="0.01666"<?php echo ($popup_freq==0.01666 ? ' selected' : ''); ?>>1 <?php _e( 'minute (WARNING!)', 'popup-modal' ); ?></option>
				<option value="0.08333"<?php echo ($popup_freq==0.08333 ? ' selected' : ''); ?>>5 <?php _e( 'minutes', 'popup-modal' ); ?></option>
				<option value="0.16666"<?php echo ($popup_freq==0.16666 ? ' selected' : ''); ?>>10 <?php _e( 'minutes', 'popup-modal' ); ?></option>
				<option value="0.25"<?php echo ($popup_freq==0.25 ? ' selected' : ''); ?>>15 <?php _e( 'minutes', 'popup-modal' ); ?></option>
				<option value="0.5"<?php echo ($popup_freq==0.5 ? ' selected' : ''); ?>>30 <?php _e( 'minutes', 'popup-modal' ); ?></option>
				<option value="1"<?php echo ($popup_freq==1 ? ' selected' : ''); ?>><?php _e( 'Hourly', 'popup-modal' ); ?></option>
				<option value="2"<?php echo ($popup_freq==2 ? ' selected' : ''); ?>>2 <?php _e( 'hours', 'popup-modal' ); ?></option>
				<option value="4"<?php echo ($popup_freq==4 ? ' selected' : ''); ?>>4 <?php _e( 'hours', 'popup-modal' ); ?></option>
				<option value="8"<?php echo ($popup_freq==8 ? ' selected' : ''); ?>>8 <?php _e( 'hours', 'popup-modal' ); ?></option>
				<option value="16"<?php echo ($popup_freq==16 ? ' selected' : ''); ?>>16 <?php _e( 'hours', 'popup-modal' ); ?></option>
				<option value="24"<?php echo ($popup_freq==24 ? ' selected' : ''); ?>><?php _e( 'Daily', 'popup-modal' ); ?></option>
				<option value="168"<?php echo ($popup_freq==168 ? ' selected' : ''); ?>><?php _e( 'Weekly', 'popup-modal' ); ?></option>
				<option value="720"<?php echo ($popup_freq==720 ? ' selected' : ''); ?>><?php _e( 'Monthly', 'popup-modal' ); ?></option>
				<option value="8760"<?php echo ($popup_freq==8760 ? ' selected' : ''); ?>><?php _e( 'Yearly', 'popup-modal' ); ?></option>
				<option value="87600"<?php echo ($popup_freq>8760 ? ' selected' : ''); ?>><?php _e( 'Once', 'popup-modal' ); ?></option>
			</select>
		</td>
	</tr>
	<tr class="megabox-prefs-expires metabox-options">
		<td><label><?php _e( 'Expires after', 'popup-modal' ); ?>:</label></td>
		<td>
			<input id="popup_expires" name="popup_expires" value="<?php echo $popup_expires; ?>" placeholder="<?php _e( 'Never', 'popup-modal' ); ?>" type="text" class="widefat date-picker" title="<?php _e( 'Format: mm-dd-yy (leave empty to never expire)', 'popup-modal' ); ?>">
		</td>
	</tr>
	<tr class="megabox-prefs-pages metabox-options">
		<td><label><?php _e( 'Display on', 'popup-modal' ); ?>:</label></td>
		<td>
			<select id="popup_pages" name="popup_pages" class="widefat">
				<option value="all"<?php echo ($popup_pages=='all' ? ' selected' : ''); ?>><?php _e( 'All pages', 'popup-modal' ); ?></option>
				<option value="front"<?php echo ($popup_pages=='front' ? ' selected' : ''); ?>><?php _e( 'Front page only', 'popup-modal' ); ?></option>
				<option value="interior"<?php echo ($popup_pages=='interior' ? ' selected' : ''); ?>><?php _e( 'Interior pages only', 'popup-modal' ); ?></option>
			</select>
		</td>
	</tr>
	<tr class="megabox-prefs-click metabox-options">
		<td><label><?php _e( 'Print option', 'popup-modal' ); ?>:</label></td>
		<td>
			<select id="popup_click" name="popup_click" class="widefat" title="<?php _e( 'Allows user to print the popup contents via a button or clicking inside the popup', 'popup-modal' ); ?>">
				<option value="close"<?php echo ($popup_click=='close' ? ' selected' : ''); ?>><?php _e( 'No', 'popup-modal' ); ?></option>
				<option value="print"<?php echo ($popup_click=='print' ? ' selected' : ''); ?>><?php _e( 'Yes', 'popup-modal' ); ?></option>
			</select>
		</td>
	</tr>
	<tr class="megabox-prefs-location metabox-options">
		<td><label><?php _e( 'Popup location', 'popup-modal' ); ?>:</label></td>
		<td>
			<select id="popup_location" name="popup_location" class="widefat">
				<option value="center"<?php echo ($popup_location=='center' ? ' selected' : ''); ?>><?php _e( 'Centered', 'popup-modal' ); ?></option>
				<option value="top"<?php echo ($popup_location=='top' ? ' selected' : ''); ?>><?php _e( 'Top center', 'popup-modal' ); ?></option>
				<option value="bottom"<?php echo ($popup_location=='bottom' ? ' selected' : ''); ?>><?php _e( 'Bottom center', 'popup-modal' ); ?></option>
			</select>
		</td>
	</tr>
	<tr class="megabox-prefs-width metabox-options">
		<td><label><?php _e( 'Max width', 'popup-modal' ); ?>:</label></td>
		<td>
			<input id="popup_width" name="popup_width" value="<?php echo (empty($popup_width)?'800':$popup_width); ?>" placeholder="800" type="text" class="widefat" title="<?php _e( 'Maximum width in pixels', 'popup-modal' ); ?>">
		</td>
	</tr>
	<tr class="megabox-prefs-title metabox-options">
		<td><label><?php _e( 'Show title', 'popup-modal' ); ?>:</label></td>
		<td>
			<select id="popup_title" name="popup_title" class="widefat">
				<option value="yes"<?php echo ($popup_title=='yes' ? ' selected' : ''); ?>><?php _e( 'Yes', 'popup-modal' ); ?></option>
				<option value="no"<?php echo ($popup_title=='no' ? ' selected' : ''); ?>><?php _e( 'No', 'popup-modal' ); ?></option>
			</select>
		</td>
	</tr>
	<tr class="megabox-prefs-color metabox-options">
		<td><label><?php _e( 'Text color', 'popup-modal' ); ?>:</label></td>
		<td>
			<input id="popup_color" name="popup_color" value="<?php echo (empty($popup_color)?'#000000':$popup_color); ?>" placeholder="#000000" type="text" class="color-picker" data-default-color="<?php echo (empty($popup_color)?'#000000':$popup_color); ?>">
		</td>
	</tr>
	<tr class="megabox-prefs-bgcolor metabox-options">
		<td><label><?php _e( 'Background', 'popup-modal' ); ?>:</label></td>
		<td>
			<input id="popup_bgcolor" name="popup_bgcolor" value="<?php echo (empty($popup_bgcolor)?'#ffffff':$popup_bgcolor); ?>" placeholder="#ffffff" type="text" class="color-picker" data-default-color="<?php echo (empty($popup_bgcolor)?'#ffffff':$popup_bgcolor); ?>">
		</td>
	</tr>
	<tr class="megabox-prefs-buttons metabox-options">
		<td><label><?php _e( 'Button location', 'popup-modal' ); ?>:</label></td>
		<td>
			<select id="popup_buttons" name="popup_buttons" class="widefat">
				<option value="no"<?php echo ($popup_buttons=='no' ? ' selected' : ''); ?>><?php _e( 'No Buttons ("X" to close)', 'popup-modal' ); ?></option>
				<option value="left"<?php echo ($popup_buttons=='left' ? ' selected' : ''); ?>><?php _e( 'Left', 'popup-modal' ); ?></option>
				<option value="center"<?php echo ($popup_buttons=='center' ? ' selected' : ''); ?>><?php _e( 'Center', 'popup-modal' ); ?></option>
				<option value="right"<?php echo ($popup_buttons=='right' ? ' selected' : ''); ?>><?php _e( 'Right', 'popup-modal' ); ?></option>
			</select>
		</td>
	</tr>
</table>
<button class="button button-secondary popup-modal-test"><?php _e( 'Test Popup', 'popup-modal' ); ?></button>
<script>
var popup_modal_admin = true;
var popup_modal_data = [{"disable":"","freq":"-1","expires":"","pages":"all","id":"-1","slug":"test"}];
jQuery('body').append( jQuery('#popupModal') );
</script>
<?php
	}
}

// Add shortcode
function teckel_popup_modal_login_form_shortcode() {
	if ( is_user_logged_in() ) return '<a class="logout" href="' . wp_logout_url() . '"><button class="btn btn-primary">' . __( 'Logout', 'popup-modal' ) . '</button></a>';
	$out = wp_login_form( array('echo' => false) );
	$out .= '<a class="lost-password" href="' . wp_lostpassword_url(get_bloginfo('url')) . '"><button class="btn btn-secondary">' . __( 'Lost password', 'popup-modal' ) . '</button></a>';
	if ( get_option( 'users_can_register' ) )
		$out .= '<a class="register" href="' . wp_registration_url() . '"><button class="btn btn-secondary">' . __( 'Register', 'popup-modal' ) . '</button></a>';
	return $out;
}

function teckel_popup_modal_init_site() {
	add_shortcode( 'pm-login', 'teckel_popup_modal_login_form_shortcode' );
}
add_action( 'init', 'teckel_popup_modal_init_site', 0 );

?>
