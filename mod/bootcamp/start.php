<?php
/**
 * Bootcamp
 * A plugin that teaches users how to use Minds.com
 * 
 * @author Mark Harding (mark@minds.com)
 */

elgg_register_event_handler('init', 'system', 'bootcamp_init');

function bootcamp_init() {
		
	elgg_extend_view('css/elgg','bootcamp/css');
	
	if(elgg_is_logged_in() && elgg_get_context() == 'news'){
		elgg_extend_view('page/elements/sidebar','bootcamp/sidebar', 1);
	}
	
	elgg_register_page_handler('bootcamp', 'bootcamp_page_handler');
	
	elgg_register_library('bootcamp', elgg_get_plugins_path() . 'bootcamp/lib/bootcamp.php');
	
	//On first login, promt user for bootcamp
	if(elgg_is_logged_in() && !elgg_get_plugin_user_setting('prompted') && !$_SESSION['fb_referrer'] && elgg_get_viewtype() != 'mobile' && strpos(current_page_url(), 'tierlogin') === false){
		elgg_set_plugin_user_setting('prompted', 'yes');
		forward('bootcamp');
	}
}

/**
 * @param array $page
 */
function bootcamp_page_handler($page)
{
	$base = elgg_get_plugins_path() . 'bootcamp/pages/bootcamp';
	
	switch ($page[0]) {
			case 'networks':
				require_once "$base/index.php";
				break;
			case 'handler':
				set_input('provider', $page[1]);
				require_once "$base/handler.php";
				break;
			case 'closewindow':
				 echo '<script type="text/javascript">
						     self.close();
						</script>';
				break;
			default:
				require_once "$base/index.php";
				break;
		}
	return true;
}
