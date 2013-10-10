<?php
/**
 * Plugin Name: Samuel L. Jackson Dummy Content
 * Plugin URI: http://johnregan3.github.io/slj-dummy-content
 * Description: Add Dummy Content to your WordPress site straight from the lips of Samuel L. Jackson.  Includes HTML elements, featured images and videos.  Deletes all dummy content with one click!  To begin, navigate to Tools > SLJ Dummy Content.
 * Author: John Regan
 * Author URI: http://johnregan3.me
 * Version: 1.0
 * Text Domain: sljdc
 *
 * Copyright 2013  John Regan  (email : johnregan3@outlook.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package SLJ Dummy Content
 * @author  John Regan
 * @version 1.0
 */
include_once( 'includes/generator.php' );

/**
 * Regsiter Scripts
 *
 * @since 1.0
 */
add_action('admin_init', 'sljdc_register_scripts');
function sljdc_register_scripts() {
	wp_register_style( 'sljdc_style', plugins_url('includes/style.css', __FILE__) );
	wp_register_script( 'sljdc_ajax', plugins_url( 'includes/sljdc-ajax.js', __FILE__ ), '1.0', true );
	wp_localize_script( 'sljdc_ajax', 'sljdc_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'sljdc_nonce' => wp_create_nonce( 'sljdc_nonce' ) ) );
}

/**
 * Enqueue Scripts
 *
 * @since 1.0
 */
function sljdc_enqueue_scripts() {
	wp_enqueue_style( 'sljdc_style' );
	wp_enqueue_script( 'sljdc_ajax' );
}

/**
 * Print direct link to Custom CSS admin page
 *
 * Fetches array of links generated by WP Plugin admin page ( Deactivate | Edit )
 * and inserts a link to the Custom CSS admin page
 *
 * @since   1.0
 * @param   array  $links  Array of links generated by WP in Plugin Admin page.
 * @return  array  $links  Array of links to be output on Plugin Admin page.
 */
$plugin = plugin_basename(__FILE__);

add_filter( "plugin_action_links_$plugin", 'sljdc_settings_link' );

function sljdc_settings_link( $links ) {
	$settings_page = '<a href="' . admin_url('tools.php?page=sljdc.php' ) .'">Settings</a>';
	array_unshift( $links, $settings_page );
	return $links;
}

/**
 * Register Options Page
 *
 * Call enqueue function only on this plugin's admin page
 *
 * @since 1.0
 */
add_action( 'admin_menu', 'sljdc_options_page' );

function sljdc_options_page() {
	$page = add_submenu_page( 'tools.php', 'SLJ Dummy Content', 'SLJ Dummy Content', 'manage_options', basename( __FILE__ ), 'sljdc_render_options' );
	add_action( 'admin_print_styles-' . $page, 'sljdc_enqueue_scripts' );
}

/**
 * Render Options Page
 *
 * @since 1.0
 */
function sljdc_render_options() {
	?>
	<div id="sljdc-settings-wrap" class="wrap">

		<div class="icon32">
			<img src="<?php echo plugins_url( 'sljdc-icon.jpg', __FILE__ ) ?>" />
		</div>

		<?php echo '<h2>' . __( 'Samuel L. Jackson Dummy Content', 'sljdc') . '</h2>' ?>

		<h3><?php _e( 'Generate Dummy Content', 'sljdc' ) ?></h3>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><?php _e( 'Posts to Generate', 'sljdc' ) ?></th>
						<td>
							<select name="sljdc_num_posts" class= "sljdc_num_posts">
								<option value="0">0</option>
								<option value="1">1</option>
								<option value="5">5</option>
								<option value="10">10</option>
								<option value="20">20</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><?php _e( 'Pages to Generate', 'sljdc' ) ?></th>
							<td>
								<select name="sljdc_num_pages" class= "sljdc_num_pages">
									<option value="0">0</option>
									<option value="1">1</option>
									<option value="5">5</option>
									<option value="10">10</option>
									<option value="20">20</option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

		<p class="generate">
			<a id="generate-content" class="button-primary" value="" ><?php _e( 'Generate Content', 'sljdc' ); ?></a><span class="spinner"></span><br />
			<div id="generate-message">&nbsp;</div>
		</p>

		<h3><?php _e( 'Delete Generated Content', 'sljdc' ) ?></h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><?php _e( 'Delete All Generated Posts, Pages & Attachments', 'sljdc' ) ?><br />
						<p class="description"><?php _e( 'If posts are manually deleted, the associated Media (Featured Image) will not be deleted from the Media Library.', 'sljdc' ) ?></p></th>
				</tr>
			</tbody>
		</table>

		<p class="delete">
			<a id="delete-content" class="button-primary" value="" ><?php _e( 'Delete Generated Content', 'sljdc' ); ?></a><span class="spinner"></span><br />
			<div id="delete-message">&nbsp;</div>
		</p>

	</div>
	<?php
}
