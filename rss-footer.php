<?php
/*
Plugin Name: RSS Footer
Version: 0.7
Plugin URI: http://www.joostdevalk.nl/wordpress/rss-footer/
Description: Allows you to add a line of content to the end of your RSS feed articles.
Author: Joost de Valk
Author URI: http://www.joostdevalk.nl/
*/

if ( ! class_exists( 'RSSFoot_Admin' ) ) {

	class RSSFooter_Admin {
		
		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_options_page('RSS Footer Configuration', 'RSS Footer', 10, basename(__FILE__), array('RSSFooter_Admin','config_page'));
			}
		}
		
		function config_page() {
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the RSS Footer options.'));
				check_admin_referer('rssfooter-config');

				if (isset($_POST['footerstring']) && $_POST['footerstring'] != "") 
					$options['footerstring'] 	= $_POST['footerstring'];
				
				if (isset($_POST['position']) && $_POST['position'] != "") 
					$options['position'] 	= $_POST['position'];
				
				if (isset($_POST['postlink'])) {
					$options['postlink'] = true;
				} else {
					$options['postlink'] = false;
				}
				
				$options['everset'] = true;
				
				$opt = serialize($options);
				update_option('RSSFooterOptions', $opt);
			}
			
			$opt  = get_option('RSSFooterOptions');
			$options = unserialize($opt);
			
			?>
			<div class="wrap">
				<h2>RSS Footer options</h2>
				<form action="" method="post" id="rssfooter-conf">
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('rssfooter-config');
					?>
					<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
					<table class="niceblue" style="width: 100%;">
						<tr valign="top">
							<th scrope="row">
								<label for="footerstring">Content to put in the footer:</label><br/>
								<small>(HTML allowed)</small>
							</th>
							<td>
								<textarea cols="80" rows="4" id="footerstring" name="footerstring"><?php echo stripslashes(htmlentities($options['footerstring'])); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scrope="row">
								<label for="position">Content position:</label>
							</th>
							<td>
								<select name="position" id="position">
									<option value="after" <?php if ($options['position'] == "after") echo 'selected="selected"'?>>after</option>
									<option value="before" <?php if ($options['position'] == "before") echo 'selected="selected"'?>>before</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scrope="row">
								Other options:
							</th>
							<td>
								<input type="checkbox" name="postlink" <?php if ($options['postlink']) echo 'checked="checked"'?>/> Include a link back to the post, with post title as anchor text too.
							</td>
						</tr>
					</table>
					<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
				</form>
			</div>
<?php		}	
	}
}

$options  = unserialize(get_option('RSSFooterOptions'));
if (!isset($options['everset'])) {
	// Set default values
	$options['footerstring'] = "Post from: <a href=\"".get_bloginfo('url')."\">".get_bloginfo('name')."</a>";
	$options['position'] = "after";
	$opt = serialize($options);
	update_option('RSSFooterOptions', $opt);
}

function embed_rssfooter($content) {
	if(is_feed()) {
		$options  = unserialize(get_option('RSSFooterOptions'));
		if ($options['position'] == "before") {
			if($options['postlink']) {
				$content = '<p><a href="'.get_the_guid().'">'.get_the_title()."</a></p>\n" . $content;	
			}
			$content = "<p>" . stripslashes($options['footerstring']) . "</p>\n" . $content;
		} else {
			$content = $content . "<p>" . stripslashes($options['footerstring']) . "</p>\n";
			if($options['postlink']) {
				$content = $content . '<p><a href="'.get_the_guid().'">'.get_the_title()."</a></p>\n";
			}
		}
	}
	return $content;
}

add_filter('the_content', 'embed_rssfooter');

add_action('admin_menu', array('RSSFooter_Admin','add_config_page'));

?>
