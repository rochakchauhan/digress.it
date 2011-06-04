<?php
add_action('admin_head-post.php', 'digressit_add_comment_change_notice');
add_action('admin_menu', 'digressit_add_admin_menu');


/**
 * 
 */
function digressit_add_comment_change_notice() {	
	
	$comments= get_approved_comments($_GET['post']);
	
	if(count($comments)){
		add_action('admin_notices', 'digressit_change_content_warning' );
	}
}

/**
 * 
 */
function digressit_change_content_warning(){
	?>
	
	<div id="register-form" class="updated error" style="padding: 5px; width: 99% <?php echo $hidethis;?>" >		
		<?php _e('Warning: There are comments attached to the structure of this page. Changing the structure of this post will break the alignment of comments to their paragraphs'); ?>
	</div>
	
	<?php
	
}


/**
 * Creates menu in the admin page. Also detects permalink status
 */
function digressit_add_admin_menu() {
	global $wp_rewrite;
	add_submenu_page( 'themes.php', 'Digress.it', 'Digress.it', 'administrator', 'digressit.php', 'digressit_theme_options_page');

	if(!$wp_rewrite->permalink_structure){
		add_action( 'admin_notices', 'digressit_permalink_required_notice' );
	}
}

/**
 *
 */
function digressit_permalink_required_notice(){
		echo "<div id='permalink-required-notice' class='updated fade'><p>".__("Warning: Digress.it requires permalinks to be enabled. Please go to <a href='").get_bloginfo('url')."/wp-admin/options-permalink.php'>".__('Permalink Settings</a> and make sure that <b>Default</b> is not selected')."</p></div>";	
}



function digressit_theme_options_page_form(){
	if($_GET['page'] == 'digressit.php' && isset($_POST['reset']) && $_POST['reset'] == 'Reset Options'){
		delete_option('digressit');
		activate_digressit();		
	}
	elseif(isset($_POST['update-digressit-options'])){
		$digressit_options = get_option('digressit');
		
		foreach($_POST as $key => $value){
			$digressit_options[$key] = $value;
		}
		
		delete_option('digressit');
		add_option('digressit', $digressit_options);
	}
}

/**
 * Creates the theme options page. Prints out HTML
 * @todo secure forms
 */
function digressit_theme_options_page() {
	global $wpdb, $digressit_content_function, $digressit_comments_function, $digressit_commentbox_function, $blog_id;
	
	digressit_theme_options_page_form();
	
	$digressit_options = get_option('digressit');
	?>

	<style>
		#wpcontent input[type=text],#wpcontent select {
		border:1px solid #DDDDDD;
		font-size:14px;
		margin:2px;
		width:auto;
		}
 		.form-table tr{
			border-bottom: 1px solid #eee;
		}	
	</style>

  	<div class="wrap" style="position: relative; font-size: 110%;">
	
		<form method="post" action="<?php $PHP_SELF; ?>">

		<h2><?php _e('Digress.it Options', 'digressit');  ?></h2>

		<table class="form-table" style="vertical-align: top; width: 800px; padding: 0; margin: 0" >

		<?php   
		$pages = null;
		foreach(get_pages() as $page){
			$pages[$page->post_title] = $page->ID;			
		}
		?>
		<tr>
			<td colspan="2"><h2><?php _e('Presentation', 'digressit'); ?></h2></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Enable for', 'digressit');  ?></b></td>
			<td>
			
				<?php digressit_print_dropdown('digressit_enabled_for', array('Pages' => 'pages', 'Posts' => 'posts'), $digressit_options['digressit_enabled_for']); ?>
				<p><?php _e("The content of this page will be the first thing a visitor to your website will see.", 'digressit'); ?></p>
			</td>
		</tr>
		
				
		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Front page content', 'digressit');  ?></b></td>
			<td>
			
				<?php digressit_print_dropdown('front_page_content', $pages, $digressit_options['front_page_content']); ?>
				<p><?php _e("The content of this page will be the first thing a visitor to your website will see.", 'digressit'); ?></p>
			</td>
		</tr>
		

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Table of Contents Label' , 'digressit');  ?></b></td>
			<td><?php digressit_print_input_text('table_of_contents_label', $digressit_options['table_of_contents_label']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Front Page Order', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('front_page_order_by', array('id' => 'id', 'date' => 'date'), $digressit_options['front_page_order_by']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Front Page Order by', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('front_page_order', array('ASC' => 'ASC', 'DESC' => 'DESC'), $digressit_options['front_page_order']); ?></td>
		</tr>



		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Comments by Section Label', 'digressit');  ?></b></td>
			<td><?php digressit_print_input_text('comments_by_section_label', $digressit_options['comments_by_section_label']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Comments by Users Label', 'digressit');  ?></b></td>
			<td><?php digressit_print_input_text('comments_by_users_label', $digressit_options['comments_by_users_label']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('General Comments Label', 'digressit');  ?></b></td>
			<td><?php digressit_print_input_text('general_comments_label', $digressit_options['general_comments_label']); ?></td>
		</tr>
		

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Keyboard Navigation', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('keyboard_navigation', array('No' => 0, 'Yes' => 1), $digressit_options['keyboard_navigation']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Enable Citation Button', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('enable_citation_button', array('No' => 0, 'Yes' => 1), $digressit_options['enable_citation_button']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Frontpage List Style', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('frontpage_list_style', array( __('Numbers', 'digressit') => 'list-style-decimal', 
																	__('None', 'digressit') => 'list-style-none',
																	__('Lower Alphabet', 'digressit') => 'list-style-lower-alpha',
																	__('Upper Alphabet', 'digressit') => 'list-style-upper-alpha',
																	__('Lower Roman', 'digressit') => 'list-style-lower-roman',
																	__('Upper Roman', 'digressit') => 'list-style-upper-roman',
																	__('Square', 'digressit') => 'list-style-square',
																	__('Circle', 'digressit') => 'list-style-circle'
																), $digressit_options['frontpage_list_style']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Allow General Comments', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('allow_general_comments', array('No' => 0, 'Yes' => 1), $digressit_options['allow_general_comments']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Enable Instant Content Search', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('enable_instant_content_search', array('No' => 'false', 'Yes' => 'true'), $digressit_options['enable_instant_content_search']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Parse List Items', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('parse_list_items', array('No' => 0, 'Yes' => 1), $digressit_options['parse_list_items']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Show Pages in Menu', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('show_pages_in_menu', array('No' => 0, 'Yes' => 1), $digressit_options['show_pages_in_menu']); ?></td>
		</tr>

		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Enable Drop Down Menu', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('enable_dropdown_menu', array('No' => 0, 'Yes' => 1), $digressit_options['enable_dropdown_menu']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Enable Sidebar', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('enable_sidebar', array('No' => 0, 'Yes' => 1), $digressit_options['enable_sidebar']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Sidebar Position', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('sidebar_position', array('Left' => 'sidebar-widget-position-left', 'Right' => 'sidebar-widget-position-right'), $digressit_options['sidebar_position']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Auto-hide Sidebar', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('auto_hide_sidebar', array('No' => 'sidebar-widget-no-auto-hide', 'Yes' => 'sidebar-widget-auto-hide'), $digressit_options['auto_hide_sidebar']); ?></td>
		</tr>
		
		<tr valign="top">
			<td style="width: 200px"><b><?php _e('In Sidebar Show', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('show_comment_count_in_sidebar', array('Comment Count' => '1', 'Section Number' => 0), $digressit_options['show_comment_count_in_sidebar']); ?></td>
		</tr>


		<tr valign="top">
			<td style="width: 200px"><b><?php _e('Custom Header Image URL', 'digressit');  ?></b></td>
			<td>
				
				<?php digressit_print_input_text('custom_header_image', $digressit_options['custom_header_image']); ?>
				<p><?php _e('This image will override the current header and will become the logo to your site. 
					Be sure to get copy the entire URL in this field. You can also 
					<a href="'.bloginfo('url').'/wp-admin/media-new.php">upload your logo</a> and get the URL from there.
					<b>Note:</b> The image needs to be a maximum of 60px tall.', 'digressit'); ?>
			</td>
		</tr>

		<tr>
			<td style="width: 200px"><b><?php _e('Custom Style Sheet', 'digressit');  ?></b></td>
			<td>
				<?php digressit_print_input_text('custom_style_sheet', $digressit_options['custom_style_sheet']); ?>
				<p><?php _e('If you would like to customize the theme, you can upload a stylesheet that can be be loaded after the required stylesheets. 
					For heavy customizations you should use the "Digress.it Wireframe" theme provided.
					For more information on this feature follow the instructions provided at', 'digressit'); ?> <a href="http://digress.it/help">http://digress.it/help</a>. </p>
			</td>
		</tr>

		<tr>
			<td colspan="2"><h2><?php _e('Advanced','digressit'); ?></h2></td>
		</tr>

		
		
		<?php if(is_super_admin()): ?>
		<tr>
			<td style="width: 200px"><b><?php _e('Debug Mode', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('debug_mode', array('No' => 0, 'Yes' => '1'), $digressit_options['debug_mode']); ?></td>
		</tr>
		
		<tr>
			<td style="width: 200px"><b><?php _e('Use CDN', 'digressit');  ?></b></td>
			<td>
			<?php digressit_print_dropdown('use_cdn', array('Yes' => '1', 'No' => 0), $digressit_options['use_cdn']); ?>
			<p><?php _e('This is an experimental feature. The idea is that you can host the media files on a really fast file server. Enabling this now
				has the risk of downloading files that are out of date. Use at your own discretion.', 'digressit'); ?></p>				
				
			</td>
		</tr>
		
		<tr>
			<td style="width: 200px"><b><?php _e('CDN');  ?></b></td>
			<td><?php digressit_print_input_text('cdn', $digressit_options['cdn'], 'disabled'); ?>

			</td>
		</tr>
		
		<?php endif; ?>
		
		
		<tr>
			<td style="width: 200px"><b><?php _e('Content Parsing Function', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('content_parser', $digressit_content_function, $digressit_options['content_parser']); ?></td>
		</tr>

		<tr>
			<td style="width: 200px"><b><?php _e('Comments Parsing Function', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('comments_parser', $digressit_comments_function, $digressit_options['comments_parser']); ?></td>
		</tr>
	
		<tr>
			<td style="width: 200px"><b><?php _e('Comment Box Parsing Function', 'digressit');  ?></b></td>
			<td><?php digressit_print_dropdown('commentbox_parser', $digressit_commentbox_function, $digressit_options['commentbox_parser']); ?></td>
		</tr>


		</table>

		<input type="hidden" name="update-digressit-options" value="1" />

		<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'digressit') ?>" />
		<input type="submit" name="reset" class="button-primary" value="<?php _e('Reset Options', 'digressit') ?>" />
		</p>

		</form>
	</div>
	

	

	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="hosted_button_id" value="XYBB4WEBLRHMN">
	<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>

	<?php 
}



?>