<?php
/*
Plugin Name: WP-Login-Vkb
Plugin URI: http://blog.bokhorst.biz/1826/computers-en-internet/wordpress-plugin-login-virtual-keyboard/
Description: Displays a virtual, on-screen keyboard to enter the wordpress password in a safer way, for example in internet cafés.
Version: 1.5.9
Author: Marcel Bokhorst
Author URI: http://blog.bokhorst.biz/about/
*/

/*
	Copyright 2009, 2010, 2011 Marcel Bokhorst

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
	jQuery JavaScript Library
		licensed under both the GNU General Public License and MIT License
		downloaded from http://jquery.com/

	JavaScript Virtual Keyboard
		by Dmitry Khudorozhkov
		licensed under CPOL (see http://www.codeproject.com/info/cpol10.aspx)
		downloaded from http://www.codeproject.com/KB/scripting/jvk.aspx

	Keyboard icon
		licensed under GPLv3 (see http://www.gnu.org/copyleft/gpl-3.0.html)
		downloaded from http://commons.wikimedia.org/wiki/File:Gnome-input-keyboard.svg
*/

#error_reporting(E_ALL);

// Handle initialize
function lvkb_init() {
	// I18n
	load_plugin_textdomain('wp-login-vkb', false, basename(dirname(__FILE__)));
}

// Create options page
function lvkb_admin_menu() {
	add_options_page(
		__('WP-Login-Vkb', 'wp-login-vkb'),
		__('WP-Login-Vkb', 'wp-login-vkb'),
		'manage_options',
		__FILE__,
		'lvkb_options');
}

// Render option page
function lvkb_options() {
?>
	<div class="wrap">
	<h2><?php _e('Login virtual keyboard', 'wp-login-vkb') ?></h2>

	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>

	<table class="form-table">

	<tr valign="top">
	<th scope="row"><?php _e('Keyboard initial visible', 'wp-login-vkb') ?></th>
	<td><input type="checkbox" name="lvkb_initial_visible" <?php if (get_option('lvkb_initial_visible')) echo 'checked="checked"'; ?>></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('Show key flash on click', 'wp-login-vkb') ?></th>
	<td><input type="checkbox" name="lvkb_key_flash" <?php if (get_option('lvkb_key_flash')) echo 'checked="checked"'; ?>></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('Show numeric key pad', 'wp-login-vkb') ?></th>
	<td><input type="checkbox" name="lvkb_num_pad" <?php if (get_option('lvkb_num_pad')) echo 'checked="checked"'; ?>></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('1-pixel gap between keys', 'wp-login-vkb') ?></th>
	<td><input type="checkbox" name="lvkb_1pixel_gap" <?php if (get_option('lvkb_1pixel_gap')) echo 'checked="checked"'; ?>></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('Virtual keyboard mandatory', 'wp-login-vkb') ?></th>
	<td><input type="checkbox" name="lvkb_mandatory" <?php if (get_option('lvkb_mandatory')) echo 'checked="checked"'; ?>></td>
	</tr>

	<tr valign="top">
	<th scope="row"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=AJSBB7DGNA3MJ&lc=US&item_name=WP%2dLogin%2dVkb&item_number=Marcel%20Bokhorst&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">
	<?php _e('I have donated to this plugin', 'wp-login-vkb') ?></a></th>
	<td><input type="checkbox" name="lvkb_donated" <?php if (get_option('lvkb_donated')) echo 'checked="checked"'; ?>></td>
	</tr>
	</table>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="lvkb_initial_visible,lvkb_key_flash,lvkb_num_pad,lvkb_1pixel_gap,lvkb_donated,lvkb_mandatory" />

	<p class="submit">
	<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wp-login-vkb') ?>" />
	</p>

	</form>
	</div>
<?php
}

// Add style sheet & JavaScript to the head of the page
function lvkb_login_head() {
	wp_enqueue_script('jquery');
	wp_deregister_script('gdsr_script');
	wp_print_scripts();
?>
	<link rel="stylesheet" type="text/css" href="<?php echo plugins_url('wp-login-vkb.css', __FILE__); ?>" />
	<script type="text/javascript" src="<?php echo plugins_url('vkboardsc.js', __FILE__); ?>"></script>
	<script type="text/javascript">
	/* <![CDATA[ */
		function lvkb_callback(ch) {
			var text = document.getElementById('user_pass');
			val = text.value;
			switch (ch) {
				case 'BackSpace':
					var min = (val.charCodeAt(val.length - 1) == 10) ? 2 : 1;
					text.value = val.substr(0, val.length - min);
					break;
				case "Enter":
					lvkb_visible = false;
					lvkb.Show(lvkb_visible);
					var form = document.getElementById('loginform');
					form.submit();
					break;
				default:
					text.value += ch;
			}
		}
	/* ]]> */
	</script>
<?php
}

// Modify the login form
function lvkb_login_form() {
?>
	<script type="text/javascript">
	/* <![CDATA[ */
		var lvkb;
		/* Decode options */
		var lvkb_visible = <?php echo get_option('lvkb_initial_visible') ? 'true' : 'false'; ?>;
		var lvkb_key_flash = <?php echo get_option('lvkb_key_flash') ? 'true' : 'false'; ?>;
		var lvkb_num_pad = <?php echo get_option('lvkb_num_pad') ? 'true' : 'false'; ?>;
		var lvkb_1pixel_gap = <?php echo get_option('lvkb_1pixel_gap') ? 'true' : 'false'; ?>;
		var lvkb_mandatory = <?php echo get_option('lvkb_mandatory') ? 'true' : 'false'; ?>;

		jQuery(document).ready(function($) {
			if (!lvkb_mandatory) {
				/* Make room for icon */
				$('#user_pass').addClass('lvkb_small');

				/* Add image to toggle keyboard */
				var lvkb_img_url = "<?php echo plugins_url('Gnome-input-keyboard.png', __FILE__); ?>";
				var lvkb_img_alt = "<?php _e('Virtual keyboard interface', 'wp-login-vkb') ?>";
				var lvkb_img_title = "<?php _e('Display virtual keyboard interface', 'wp-login-vkb') ?>";
				var lvkb_img = $('<img id="lvkb_img" src="' + lvkb_img_url + '" alt="' + lvkb_img_alt + '" title="' + lvkb_img_title + '">');
				$('#user_pass').after(lvkb_img);

				/* Handle image click */
				lvkb_img.click(function() {
					lvkb_visible = !lvkb_visible;
					lvkb.Show(lvkb_visible);
				});
			}

			/* Add container for keyboard */
			$('#user_pass').parent().parent().after($('<div id="lvkb_div">'));

			/* Create virtual keyboard */
			lvkb = new VKeyboard('lvkb_div',	// container's id, mandatory
				lvkb_callback,					// reference to callback function, mandatory
												// (this & following parameters are optional)
				false,							// create the arrow keys or not?
				false,							// create up and down arrow keys?
				false,							// reserved
				lvkb_num_pad,					// create the numpad or not?
				"",								// font name ("" == system default)
				"12px",							// font size in px
				"#000",							// font color
				"#F00",							// font color for the dead keys
				"#FFF",							// keyboard base background color
				"#FFF",							// keys' background color
				"#DDD",							// background color of switched/selected item
				"#777",							// border color
				"#CCC",							// border/font color of "inactive" key (key with no value/disabled)
				"#FFF",							// background color of "inactive" key (key with no value/disabled)
				"#F77",							// border color of language selector's cell
				lvkb_key_flash,					// show key flash on click? (false by default)
				"#CC3300",						// font color during flash
				"#FF9966",						// key background color during flash
				"#CC3300",						// key border color during flash
				false,							// embed VKeyboard into the page?
				lvkb_1pixel_gap,				// use 1-pixel gap between the keys?
				0);								// index (0-based) of the initial layout
			lvkb.Show(lvkb_visible);

			/* Make virtual keyboard mandatory */
			if (lvkb_mandatory) {
				$('#user_pass').attr('readonly', true);
				$('#user_pass').focus(function() {
					lvkb.Show(true);
				});
				$('#user_pass').blur(function() {
					lvkb.Show(false);
				});
			}
		});
	/* ]]> */
	</script>
<?php
}

// Register the defined actions
if (function_exists('add_action')) {
	add_action('init', 'lvkb_init');
	add_action('login_head', 'lvkb_login_head');
	add_action('login_form', 'lvkb_login_form');
	add_action('admin_menu', 'lvkb_admin_menu');
}

?>
