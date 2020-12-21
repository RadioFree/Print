<?php
/**
 * Plugin Name: Radio Free - Print
 * Plugin URI: https://llacuna.org
 * Description: This plugin adds a button to the bottom of each post that adds the ability to make any post printable in a two column journal style layout. It includes automatic citations for links, including in-line annotations.   
 * Version: 1.0
 * Author: Sufi Shaikh & Chase Lang
 * Author URI: https://llacuna.org
 **/
 
function pa_enqueue_scripts() {
	wp_enqueue_style( 'pa_style', plugin_dir_url( __FILE__ ) . '/assets/admin.css');
}	
 
add_action('admin_enqueue_scripts', 'pa_enqueue_scripts');
 
function pa_add_to_content( $content ) {
	global $wp;
    if( is_single() && !$wp->query_vars['printer_app'] ) {
		$pa_settings = @unserialize(get_option( 'pa_settings' ));
		$txt = $pa_settings['btn_text'] ? $pa_settings['btn_text'] : 'Print Preview';
		$txt_color = $pa_settings['color'] ? $pa_settings['color'] : '#fff';
		$txt_bg = $pa_settings['bg_color'] ? $pa_settings['bg_color'] : '#178fc7';
		
		$content .= '<a href="?printer_app=1" class="ap-print-btn" style=" padding: 10px; margin-top: 10px; display: block; width: 140px; text-align: center; border-radius: 50px; font-size: 14px; box-shadow: none !important; background-color: ' .$txt_bg. '; color: ' .$txt_color. ';">' .$txt. '</a>';
    }
    return $content;
}
add_filter( 'the_content', 'pa_add_to_content' );

function pa_add_query_vars($vars) {
    return array('printer_app') + $vars;
}
add_filter('query_vars', 'pa_add_query_vars');

function pa_template($template) {
  global $wp;
  if ( @$wp->query_vars['printer_app'] && is_single() ) {
    return dirname( __FILE__ ) . '/template/print_temp.php';
  }
  else {
    return $template;
  }
}
add_filter('single_template', 'pa_template');

function pa_plugin_page() {
	add_menu_page( 'Radio Free', 'Radio Free', 'manage_options', 'radio-free', 'radiofreePluginsPage', plugin_dir_url( __FILE__ ) . 'icon.jpg' );
	add_submenu_page( 'radio-free', 'Print Settings', 'Print', 'manage_options', 'rf_print', 'pa_settings_render' );
}

add_action( 'admin_menu', 'pa_plugin_page' );

function radiofreePluginsPage() {
	echo '<br><b>Radio Free is comitted to creating free and open source tools for journalists and publishers.  For more informtion, visit <a href="https://radiofree.org">radiofree.org</a></b>';
}

function pa_settings_render() { ?>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		<?php
			if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pa_settings']) ){
				$pa_settings = $_POST['pa_settings'];
				update_option( 'pa_settings', serialize($pa_settings) );
			}
			$pa_settings = @unserialize(get_option( 'pa_settings' ));
		?>
		<div class="ap_settings_row">
			<label>Favicon URL: </label>
			<input type="text" value="<?php echo @$pa_settings['favicon_url']; ?>" name="pa_settings[favicon_url]"/>
		</div>
		
		<div class="ap_settings_row">
			<label>General Description: </label>
			<textarea name="pa_settings[desc]"><?php echo @$pa_settings['desc']; ?></textarea>
		</div>
		
		<div class="ap_settings_row">
			<label>Text Before Author: </label>
			<input type="text" value="<?php echo @$pa_settings['txt_before_author']; ?>" name="pa_settings[txt_before_author]"/>
		</div>
		
		<div class="ap_settings_row">
			<label>Print Button Text: </label>
			<input type="text" value="<?php echo @$pa_settings['btn_text']; ?>" name="pa_settings[btn_text]"/>
		</div>
		
		<div class="ap_settings_row">
			<label>Print Button Background Color: </label>
			<input type="text" value="<?php echo @$pa_settings['bg_color']; ?>" name="pa_settings[bg_color]"/>
		</div>
		
		<div class="ap_settings_row">
			<label>Print Button Text Color: </label>
			<input type="text" value="<?php echo @$pa_settings['color']; ?>" name="pa_settings[color]"/>
		</div>
		
		<input type="submit" value="Save Settings" class="button button-primary"/>
	</form>
	<?php
}


function show_reference_links($contents){
	//$post_link = array();
	
	if(is_single()){
		if ( preg_match_all('/<a (.+?)>/', $contents, $matches) ) {
			$post_link = array();
			foreach ($matches[1] as $match) {
				foreach ( wp_kses_hair($match, array('http','https')) as $attr) {
					if($attr['name'] == 'href') $post_link[]['link'] = $attr['value'];
				}
			}
			//$post_link[] = $link['href'];
		}
	}
	
	if($post_link){
		$ind = 0;
		$html = '<div class="further-read-details"><h3>Citations</h3>';
		foreach($post_link as $link){
			if( get_title_by_link($link["link"]) !== '' ) {
				$html .= '<span class="refer-number"><b style="font-weight: bold;">['. ++$ind .']</b> '.get_title_by_link($link["link"]).' &#10148; '. '<a href="'.$link["link"].'">'.$link["link"].'</a></span>';
			}else{
				$html .= '<span class="refer-number"><b style="font-weight: bold;">['. ++$ind .']</b> &#10148; '. ' <a href="'.$link["link"].'">'.$link["link"].'</a></span>';
			}
		}
		$html .= '</div>';
	}
	
	echo $html;
}

function get_title_by_link($url){
	$fp = @file_get_contents($url);
 
	if (!$fp) 
		return '';

	$res = preg_match("/<title>(.*)<\/title>/siU", $fp, $title_matches);
	if (!$res) 
		return ''; 

	// Clean up title: remove EOL's and excessive whitespace.
	$title = preg_replace('/\s+/', ' ', $title_matches[1]);
	$title = trim($title);
	return $title;
}
