<?php
/*
Plugin Name: RSS Footer
Version: 0.9.2
Plugin URI: http://yoast.com/wordpress/rss-footer/
Description: Allows you to add a line of content to the end of your RSS feed articles.
Author: Joost de Valk
Author URI: http://yoast.com/
*/

if ( ! class_exists( 'RSSFooter_Admin' ) ) {

	require_once('yst_plugin_tools.php');

	class RSSFooter_Admin extends Yoast_Plugin_Admin {
		
		var $hook 		= 'rss-footer';
		var $longname	= 'RSS Footer Configuration';
		var $shortname	= 'RSS Footer';
		var $filename	= 'rss-footer/rss-footer.php';
		var $ozhicon	= 'feed_edit.png';
		
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
				
				$options['everset'] = 2;
				
				update_option('RSSFooterOptions', $options);
			}
			
			$options  = get_option('RSSFooterOptions');
			
			?>
			<div class="wrap">
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://cdn.yoast.com/theme/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
				<h2>RSS Footer options</h2>
				<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<form action="" method="post" id="rssfooter-conf">
							<?php
							if ( function_exists('wp_nonce_field') )
								wp_nonce_field('rssfooter-config');
								
							$rows = array();
							$rows[] = array(
								"id" => "footerstring",
								"label" => "Content to put in the footer",
								"desc" => "(HTML allowed)",
								"content" => '<textarea cols="50" onchange="javascript:updatePreview();" rows="10" id="footerstring" name="footerstring">'.stripslashes(htmlentities($options['footerstring'])).'</textarea>',
							);
							$rows[] = array(
								"label" => 'Explanation',
								"content" => 'You can use %%POSTLINK%% within the content, this will be replaced by a link to the post with the title of the post as the anchor text. If you update the text above, check the preview below.'
							);
							$this->postbox('rssfootercontent','Content of your RSS Footer',$this->form_table($rows));
							$this->postbox('rssfooterpreview','Preview of your RSS Footer','<div id="preview">You need JavaScript enabled for the preview to work.</div><script type="text/javascript" charset="utf-8">
								function nl2br(str) {
									return (str + \'\').replace(/([^>]?)\n/g, \'$1\'+ \'<br/>\' +\'\n\');
								}
								jQuery("#footerstring").change( function() {
									var text = jQuery("#footerstring").val();
									text = text.replace("%%POSTLINK%%","<a href=#>Test post</a>");
									jQuery("#preview").html(nl2br(text));									
								}).change();
							</script>');
							$rows = array();
							$rows[] = array(
								"id" => "position",
								"label" => "Content position",
								"content" => '<select name="position" id="position">
									<option value="after" '.selected($options['position'],"after",false).'>after</option>
									<option value="before" '.selected($options['position'],"before",false).'>before</option>
								</select>',
							);
							$rows[] = array(
								"label" => 'Explanation',
								"content" => 'The position determines whether the content you\'ve entered above will appear below or above the post.'
							);
							$this->postbox('rssfootersettings','Settings',$this->form_table($rows));
							?>
							<div class="submit">
								<input type="submit" class="button-primary" name="submit" value="Update RSS Footer Settings &raquo;" />
							</div>
							</form>
						</div>
					</div>
				</div>
				<div class="postbox-container" style="width:20%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<?php
								$this->plugin_like();
								$this->plugin_support();
								$this->news(); 
							?>
						</div>
						<br/><br/><br/>
					</div>
				</div>
			</div>
<?php		}	
	}
	$rssfoot = new RSSFooter_Admin();
}

$options  = get_option('RSSFooterOptions');
if (!isset($options['everset'])) {
	// Set default values
	$options['footerstring'] = "%%POSTLINK%% is a post from: <a href=\"".get_bloginfo('url')."\">".get_bloginfo('name')."</a>";
	$options['position'] = "after";
	update_option('RSSFooterOptions', $options);
} elseif ($options['everset'] === true) {
	if ($options['position'] == "after") {
		$options['footerstring'] = $options['footerstring']."<br/><br/>%%POSTLINK%%";
	} else {
		$options['footerstring'] = "%%POSTLINK%%<br/><br/>".$options['footerstring'];
	}
	$options['everset'] = 2;
	update_option('RSSFooterOptions', $options);
} 

function embed_rssfooter($content) {
	if(is_feed()) {
		$options  = get_option('RSSFooterOptions');
		$postlink = '<a href="'.get_permalink().'">'.get_the_title()."</a>";
		
		$rssfootcontent = stripslashes($options['footerstring']);
		$rssfootcontent = str_replace("%%POSTLINK%%",$postlink,$rssfootcontent);
		
		if ($options['position'] == "before") {
			if($options['postlink']) {
				$content = '<p><a href="'.get_permalink().'">'.get_the_title()."</a></p>\n" . $content;	
			}
			$content = "<p>" . $rssfootcontent . "</p>\n" . $content;
		} else {
			$content = $content . "<p>" . $rssfootcontent . "</p>\n";
			if($options['postlink']) {
				$content = $content . "<p>".$postlink."</p>\n";
			}
		}
	}
	return $content;
}

add_filter('the_content', 'embed_rssfooter');
add_filter('the_excerpt_rss', 'embed_rssfooter');

?>