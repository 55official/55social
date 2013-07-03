<?php
/**
 * Minds
 *
 * @package Minds
 * @author Kramnorth (Mark Harding)
 *
 */

function minds_init(){
// 	$i = 10000;
// 	$mt = microtime(true);
// 	while($i-->0) {
// 		$sql = 'SHOW MASTER STATUS';
// 		global $DB_QUERY_CACHE;
// 		$DB_QUERY_CACHE = array();
// 		$data = get_data($sql);
// 	}
// 	var_dump(microtime(true) - $mt, $data);
	
	elgg_register_simplecache_view('minds');	
	
	elgg_register_event_handler('pagesetup', 'system', 'minds_pagesetup');
	
	elgg_register_page_handler('news', 'minds_news_page_handler');
		
 	elgg_extend_view('page/elements/head','minds/meta');
	
	elgg_extend_view('page/elements/head','minds/analytics', 90000); //such a large number so it is always at the bottom
	
	elgg_extend_view('register/extend', 'minds/register_extend', 500);
	
	//Register the minds elastic news library (to override the default elgg river)
	elgg_register_library('elastic_news', elgg_get_plugins_path().'minds/lib/elastic_news.php');
	elgg_load_library('elastic_news');
	
	//put the quota in account statistics
	elgg_extend_view('core/settings/statistics', 'minds/quota/statistics', 500);
	
	//register the ubuntu font
	elgg_register_css('ubuntu.font', 'http://fonts.googleapis.com/css?family=Ubuntu:300');
	elgg_load_css('ubuntu.font');

	//register our own css files
	$url = elgg_get_simplecache_url('css', 'minds');
	elgg_register_css('minds.default', $url);	
	
	//register our own js files
	$minds_js = elgg_get_simplecache_url('js', 'minds');
	elgg_register_js('minds.js', $minds_js);
	
	//plugin for cookie manipulation via JS
	elgg_register_js('jquery-cookie', elgg_get_config('wwwroot').'mod/minds/vendors/jquery-cookie/jquery.cookie.js');
	elgg_load_js('jquery-cookie');
	
	//register inline js player
	$uiVideoInline = elgg_get_simplecache_url('js', 'uiVideoInline');
	elgg_register_js('uiVideoInline', $uiVideoInline);
	
	//register textarea expander
	elgg_register_js('jquery.autosize', elgg_get_site_url() . 'mod/minds/vendors/autosize/jquery.autosize.js');
	
	//register carousel js
	elgg_register_js('carouFredSel', elgg_get_site_url() . 'mod/minds/vendors/carouFredSel/jquery.carouFredSel-6.2.0.js');
	
	elgg_unregister_js('jquery');
	elgg_register_js('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js', 'head');
	elgg_load_js('jquery');
	
	//register jquery.form
	elgg_register_js('jquery.form', elgg_get_site_url() . 'mod/minds/vendors/jquery/jquery.form.js');
	elgg_load_js('jquery.form');
	
	//registers tipsy
	elgg_register_js('jquery.tipsy', elgg_get_site_url() . 'mod/minds/vendors/tipsy/src/javascripts/jquery.tipsy.js');
	elgg_load_js('jquery.tipsy');
	elgg_register_css('tipsy', elgg_get_site_url() . 'mod/minds/vendors/tipsy/src/stylesheets/tipsy.css');
	elgg_load_css('tipsy');
		
	//set the custom index
	elgg_register_plugin_hook_handler('index', 'system','minds_index');
	//make sure users agree to terms
	elgg_register_plugin_hook_handler('action', 'register', 'minds_register_hook');
	
	//add an infinite rather than buttons
	elgg_extend_view('navigation/pagination', 'minds/navigation');
	elgg_register_ajax_view('page/components/ajax_list');
	
	elgg_register_plugin_hook_handler('register', 'menu:river', 'minds_river_menu_setup');
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'minds_entity_menu_setup');
	
	//setup the generic upload endpoint
	elgg_register_page_handler('upload', 'minds_upload');
	
	//unregister the register page and register this new one
	elgg_register_page_handler('register', 'minds_register_page_handler');
	
	//setup the licenses pages
	elgg_register_page_handler('licenses', 'minds_license_page_handler');
	
	//add cache headers to pages for logged out users
	elgg_register_plugin_hook_handler('route', 'all', 'minds_route_page_handler_cache', 100);
	elgg_register_plugin_hook_handler('index', 'system', 'minds_route_page_handler_cache', 100);
	
	//setup the tracking of user quota - on a file upload, increment, on delete, decrement
	elgg_register_event_handler('create', 'object', 'minds_quota_increment');
	elgg_register_event_handler('delete', 'object', 'minds_quota_decrement');
	
	//subscribe users to the minds channel once they register
	elgg_register_plugin_hook_handler('register', 'user', 'minds_subscribe_default', 1);
	
	if(elgg_is_active_plugin('htmlawed')){
		//Add to HTMLawed so that we can allow embedding
		elgg_unregister_plugin_hook_handler('validate', 'input', 'htmlawed_filter_tags');
		elgg_register_plugin_hook_handler('validate', 'input', 'minds_htmlawed_filter_tags', 1);
	}
	
	//needs to be loaded after htmlawed
	//this is for allow html <object> tags
	$CONFIG->htmlawed_config['safe'] = false;
	
	$actionspath = elgg_get_plugins_path() . "minds/actions";
	elgg_register_action("minds/feature","$actionspath/minds/feature.php");
	elgg_register_action("minds/river/delete", "$actionspath/river/delete.php");
	elgg_register_action("minds/upload", "$actionspath/minds/upload.php");
	elgg_register_action("minds/remind", "$actionspath/minds/remind.php");
	elgg_register_action("minds/remind/external", "$actionspath/minds/remind_external.php");
	elgg_register_action("friends/add", "$actionspath/friends/add.php", "public");
	elgg_register_action("embed/youtube", "$actionspath/embed/youtube.php");
        elgg_register_action("registernode","$actionspath/minds/registernode.php");
        elgg_register_action("registernewnode","$actionspath/minds/registernewnode.php");
        elgg_register_action("select_free_tier","$actionspath/minds/select_free_tier.php");
	
	if(elgg_get_context() == 'oauth2'){
		pam_auth_usertoken();//auto login users if they are using oauth step1
	}
	//make sure all users are subscribed to minds, only run once.
	//run_function_once('minds_subscribe_bulk');
        
        
        
        // Handle some tier pages
        
        // Extend public pages
        elgg_register_plugin_hook_handler('public_pages', 'walled_garden', function ($hook, $handler, $return, $params){
            $pages = array('tierlogin'); 
            return array_merge($pages, $return);
        });
        
        // Endpoint
        elgg_register_page_handler('tierlogin', function($pages) {
            
            $_SESSION['fb_referrer'] = 'y'; // Prevent Bootcamp intercepting login
            $_SESSION['__tier_selected'] = get_input('tier');
            $content = "<div class=\"register-popup\">".elgg_view_form('register', null, array('returntoreferer' => true))."</div>";
            
            // If we've returned to the window after a successful login, then refresh back to parent
            if (elgg_is_logged_in()) {
                $content .= "
                <script>
                    window.opener.location.reload();  

                    window.close();
                </script>
                ";
            }
            
            $params = array(
                'title' => elgg_echo('minds_widgets:tab:'.$tab),
                'content' => $content,
                'sidebar' => ''
            );
            
            echo elgg_view_page('Login', elgg_view_layout('default', $params));
            return true;
        });
        
        // Override the return url on tier orders
        elgg_register_plugin_hook_handler('urls', 'pay', function($hook, $type, $return, $params) {
            
            if ($order = $params['order']) {
            
                $items = unserialize($order->items);
                if ($items) {
                    // Assume that if the first one is a tier then everything is
                    $ia = elgg_set_ignore_access();
                    
                    $tier = get_entity($items[0]->object_guid);
                    if (elgg_instanceof($tier, 'object', 'minds_tier'))
                            $return['return'] = elgg_get_site_url() . 'register/node/';
                            
                    elgg_set_ignore_access($ia);
             
                    return $return;
                }
                
            }
            
        });
}

function minds_index($hook, $type, $return, $params) {
	if ($return == true) {
		// another hook has already replaced the front page
		return $return;
	}
	
	if(!include_once(dirname(__FILE__) . '/pages/index.php')){
		return false;
	}
	
	return true;
}

/**
 * Page handler for news
 *
 * @param array $page
 * @return bool
 * @access private
 */
function minds_news_page_handler($page) {
	global $CONFIG;

	elgg_set_page_owner_guid(elgg_get_logged_in_user_guid());

	// make a URL segment available in page handler script
	$page_type = elgg_extract(0, $page, 'friends');
	$page_type = preg_replace('[\W]', '', $page_type);
	if ($page_type == 'owner') {
		$page_type = 'mine';
	}
	set_input('page_type', $page_type);

	// content filter code here
	$entity_type = '';
	$entity_subtype = '';

	require_once("pages/river.php");
	return true;
}
/**
 * Page handler for register page
 *
 * @param array $page
 * @return bool
 * @access private
 */
function minds_register_page_handler($page) {
        if (isset($page[0]))
        {
            switch ($page[0])
            {
                case 'node':
                    $base_dir = elgg_get_plugins_path().'minds/pages/account';
                    require_once("$base_dir/node.php");
                    break;
                case 'testping':
                    $base_dir = elgg_get_plugins_path().'minds/pages/account';
                    require_once("$base_dir/testping.php");
                    break;
            }
        }
        else
        {
            $base_dir = elgg_get_plugins_path().'minds/pages/account';
            require_once("$base_dir/register.php");
        }
	return true;
}

function minds_route_page_handler_cache($hook, $type, $returnvalue, $params) {
	if (!elgg_is_logged_in()) {
		$handler = elgg_extract('handler', $returnvalue);
// 		$page = elgg_extract('segments', $returnvalue);
// 		header('Expires: ' . date('r', time() + 300), true);//cache for 5min
// 		header("Pragma: public", true);
// 		header("Cache-Control: public", true);
		if (!in_array($handler, array('js', 'css', 'photos'))) {
			header("X-No-Client-Cache: 1", true);
		}
	}
}

function minds_register_hook()
{
	if (get_input('terms',false) != 'true') {
		register_error(elgg_echo('minds:register:terms:failed'));
		forward(REFERER);
	}
	
	return true;
}


function minds_pagesetup(){
	$user = elgg_get_logged_in_user_entity();
	//Top Bar Menu
	elgg_unregister_menu_item('topbar', 'elgg_logo');
	elgg_unregister_menu_item('topbar', 'administration');
	elgg_unregister_menu_item('topbar', 'friends');
	
	if(elgg_get_context()!='main')	{
		elgg_register_menu_item('topbar', array(
			'name' => 'search',
			'href' => false,
			'text' => elgg_view('minds_search/header'),
			'priority' => 1,
			//'section' => 'alt',
		));
	
		elgg_register_menu_item('topbar', array(
			'name' => 'minds_logo',
			'href' => elgg_get_site_url(),
			'text' => '<img src=\''. elgg_get_site_url() . 'mod/minds/graphics/minds_logo_transparent.png\' class=\'minds_logo\'>',
			'priority' => 0
		));
	}
	
	//rename activity news	
	elgg_unregister_menu_item('site', 'activity');
	
	$item = new ElggMenuItem('news', elgg_echo('news'), 'news');
	if($user)
	elgg_register_menu_item('site', array(
						'name' => 'news',
						'href' => 'news',
						'text' => '&#59194;',
						'title' => elgg_echo('news'),
						'class' => 'entypo'
					));
	
	if($user){		
		elgg_register_menu_item('site', array(
						'name' => elgg_echo('minds:upload'),
						'href' => 'archive/upload',
						'text' => '&#128228;',
						'title' => elgg_echo('minds:upload'),
						'class' => 'entypo'
					));
	}
		
	
	//RIGHT MENU	
	//profile
	elgg_unregister_menu_item('topbar', 'profile');
	if($user)
	elgg_register_menu_item('topbar', array(
			'name' => 'profile',
			'href' => '/profile/' . elgg_get_logged_in_user_entity()->username,
			'class'=> 'profile',
			'text' => elgg_view_entity_icon(elgg_get_logged_in_user_entity(), 'tiny', array('use_hover'=>false)),
			'priority' => 60,
			'section' => 'alt',
		));
	//settings
	elgg_unregister_menu_item('topbar', 'usersettings');
	if($user)
	elgg_register_menu_item('topbar', array(
			'name' => 'usersettings',
			'href' => '/settings/user/' . $user->username,
			'text' => '&#9881;',
			'title' => elgg_echo('settings'),
			'class' => 'entypo',
			'priority' => 800,
			'section' => 'alt',
		));
	if(!$user){
		elgg_register_menu_item('topbar', array(
			'name' => 'register',
			'href' => '/register',
			'text' => elgg_echo('register'),
			'priority' => 900,
			'section' => 'alt',
		));
		elgg_register_menu_item('topbar', array(
			'name' => 'login',
			'href' => '#',
			'text' => elgg_view('core/account/login_dropdown'),
			'priority' => 1000,
			'section' => 'alt',
		));
	} else {
		elgg_unregister_menu_item('topbar', 'logout');
		elgg_register_menu_item('topbar', array(
			'name' => 'logout',
			'href' => 'action/logout',
			'text' => '&#59278;',
			'title' => elgg_echo('logout'),
			'class' => 'entypo',
			'priority' => 1000,
			'section' => 'alt',
		));
	}
	
	// embed support
        $item = ElggMenuItem::factory(array(
                'name' => 'youtube',
                'text' => elgg_echo('minds:embed:youtube'),
                'priority' => 15,
                'data' => array(
                        'view' => 'embed/youtube'
                ),
        ));
        elgg_register_menu_item('embed', $item);
}

function minds_upload($page){
	include(dirname(__FILE__) . "/pages/inline_upload.php");
	return true;
}

/*
 * License Page
 */
function minds_license_page_handler($page){
	include(dirname(__FILE__) . "/pages/license.php");
	return true;
}
		
function minds_quota_increment($event, $object_type, $object) {
	
	$user = elgg_get_logged_in_user_entity();
	
	if(($object->getSubtype() == "file") || ($object->getSubtype() == "image")){
		if($object->size){
			$user->quota_storage = $user->quota_storage + $object->size;
			
			$user->save();
		}
		
	} 
	return;
}

function minds_quota_decrement($event, $object_type, $object) {
	if(($object->getSubtype() == "file") || ($object->getSubtype() == "image")){
		echo $object->size;
	}
	
	if($object->getSubtype() == "kaltura_video"){
		//we need to do kaltura differently because it is a remote uplaod
		require_once(dirname(dirname(__FILE__)) ."/kaltura_video/kaltura/api_client/includes.php");
		
	}
	return;

}


/**
 * Edit the river menu defaults
 */
function minds_river_menu_setup($hook, $type, $return, $params) {
	if (elgg_is_logged_in()) {
		$item = $params['item'];
		$object = $item->getObjectEntity();
		$subject = $item->getSubjectEntity();
		
		//Delete button
		elgg_unregister_menu_item('river', 'delete'); 
		if ($subject->canEdit() || $object->canEdit()) {
			$options = array(
				'name' => 'delete',
				'href' => "action/minds/river/delete?id=$item->id",
				'text' => '&#10062;',
				'title' => elgg_echo('delete'),
				'class' => 'entypo',
				'confirm' => elgg_echo('deleteconfirm'),
				'is_action' => true,
				'priority' => 200,
			);
			$return[] = ElggMenuItem::factory($options);
		}
		
		$allowedReminds = array('wallpost', 'kaltura_video', 'album', 'image', 'tidypics_batch', 'blog');
		//Remind button
		if(in_array($object->getSubtype(), $allowedReminds)){
			$options = array(
					'name' => 'remind',
					'href' => "action/minds/remind?guid=$object->guid",
					'text' => '&#59159;',
					'title' => elgg_echo('minds:remind'),
					'class' => 'entypo',
					'is_action' => true,
					'priority' => 1,
				);
			$return[] = ElggMenuItem::factory($options);
		}
	}

	return $return;
}
/**
 * Edit the river menu defaults
 */
function minds_entity_menu_setup($hook, $type, $return, $params) {
	if (elgg_is_logged_in()) {

		$entity = $params['entity'];
		$handler = elgg_extract('handler', $params, false);
		
		$allowedReminds = array('wallpost', 'kaltura_video', 'album', 'image', 'tidypics_batch', 'blog');
		//Remind button
		if(in_array($entity->getSubtype(), $allowedReminds)){
				$options = array(
						'name' => 'remind',
						'href' => "action/minds/remind?guid=$entity->guid",
						'text' => '&#59159;',
						'title' => elgg_echo('minds:remind'),
						'class' => 'entypo',
						'is_action' => true,
						'priority' => 1,
					);
				$return[] = ElggMenuItem::factory($options);	
		}
		//Delete button
		elgg_unregister_menu_item('entity', 'delete'); 
		if ($entity->canEdit()) {
			$options = array(
				'name' => 'delete',
				'href' => "action/$handler/delete?guid={$entity->getGUID()}",
				'text' => '&#10062;',
				'title' => elgg_echo('delete'),
				'class' => 'entypo',
				'confirm' => elgg_echo('deleteconfirm'),
				'is_action' => true,
				'priority' => 200,
			);
			$return[] = ElggMenuItem::factory($options);
		}
	}
	if(elgg_is_admin_logged_in()){
		if($entity instanceof ElggObject){
			//feature button
			$options = array(
						'name' => 'feature',
						'href' => "action/minds/feature?guid=$entity->guid",
						'text' => $entity->featured ? elgg_echo('un-feature') : elgg_echo('feature'),
						'title' => elgg_echo('feature'),
						'is_action' => true,
						'priority' => 2,
					);
			$return[] = ElggMenuItem::factory($options);
		}	
	}

	return $return;
}

/**
 * Replace urls, hash tags, and @'s by links
 * 
 * @param string $text The text
 * @return string
 */
function minds_filter($text) {
	global $CONFIG;

	$text = ' ' . $text;

	// email addresses
	$text = preg_replace(
				'/(^|[^\w])([\w\-\.]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})/i',
				'$1<a href="mailto:$2@$3">$2@$3</a>',
				$text);

	// links
	$text = parse_urls($text);

	// usernames
	$text = preg_replace(
				'/(^|[^\w])@([\p{L}\p{Nd}._]+)/u',
				'$1<a href="' . $CONFIG->wwwroot . 'channel/$2">@$2</a>',
				$text);

	// hashtags
	$text = preg_replace(
				'/(^|[^\w])#(\w*[^\s\d!-\/:-@]+\w*)/',
				'$1<a href="' . $CONFIG->wwwroot . 'search/?q=$2">#$2</a>',
				$text);

	$text = trim($text);

	return $text;
}

/*
 * New users are automatically subscribed to the minds channel
 */
function minds_subscribe_default($hook, $type, $value, $params){
	$user = elgg_extract('user', $params);

	// no clue what's going on, so don't react.
	if (!$user instanceof ElggUser) {
		return;
	}

	// another plugin is requesting that registration be terminated
	// no need for uservalidationbyadmin
	if (!$value) {
		return $value;
	}
	
	$minds = get_user_by_username('minds');
	
	$user->addFriend($minds->guid);
	
	return $value;
}
/**
 * Bulk user subscribe to channel
 */
function minds_subscribe_bulk($username = 'minds'){
	$u = get_user_by_username($username);
	$users = elgg_get_entities(array('type'=>'user', 'limit'=>0));
	$i = 0;
	foreach($users as $user){
		if(!$user->isFriend()){
			$user->addFriend($u->guid);
			$i++;
			//if 25 users have been done, sleep for 1 second and then carry on - stops db overload
			if($i % 20 == 0){
				sleep(1);
			}
		}
	}
}

function minds_fetch_image($description, $owner_guid) {
  
  global $post, $posts;
  $fbimage = '';
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',$description, $matches);
  $image = $matches [1] [0];
 
  if(empty($image)) {
    //$image = elgg_get_site_url() . 'mod/minds/graphics/minds_logo.png';
    if($owner_guid){
   	 	$owner = get_entity($owner_guid);
    	$image = $owner->getIconURL('large');
	}
  }
  
  return $image;
}

function minds_get_featured($type, $limit = 5, $output = 'entities'){
	global $CONFIG;
	if (class_exists(elasticsearch)) {
		$es = new elasticsearch();
		$es->index = $CONFIG->elasticsearch_prefix . 'featured';
		$data = $es->query($type,null, 'time_stamp:desc', $limit, 0, array('age'=>3600));
		foreach($data['hits']['hits'] as $item){
			$guids[] = intval($item['_id']);
		}
		if(count($guids) > 0){
			if($output == 'entities'){
				return elgg_get_entities(array('guids'=>$guids, 'limit'=>$limit));
			} elseif($output == 'guids'){
				return $guids;
			}
		}
	}
	return false;
}

 /* Extend / override htmlawed */ 
function minds_htmlawed_filter_tags($hook, $type, $result, $params) {
	if(strpos($_SERVER['REQUEST_URI'], 'action/plugins/usersettings/save') !== FALSE){
		$extraALLOW = 'script';
	}
	
	$var = $result;

	elgg_load_library('htmlawed');

	$htmlawed_config = array(
		// seems to handle about everything we need.
		'safe' => 0,
		'deny_attribute' => 'on*',
		'comments'=>0,
		'cdata'=>0,
		'hook_tag' => 'htmlawed_tag_post_processor',
		'elements'=>'*-applet-script,'.$extraALLOW, // object, embed allowed
		'schemes' => '*:http,https,ftp,news,mailto,rtsp,teamspeak,gopher,mms,callto',
		// apparent this doesn't work.
		// 'style:color,cursor,text-align,font-size,font-weight,font-style,border,margin,padding,float'
	);

	// add nofollow to all links on output
	if (!elgg_in_context('input')) {
		$htmlawed_config['anti_link_spam'] = array('/./', '');
	}

	$htmlawed_config = elgg_trigger_plugin_hook('config', 'htmlawed', null, $htmlawed_config);

	if (!is_array($var)) {
		$result = htmLawed($var, $htmlawed_config);
	} else {
		array_walk_recursive($var, 'htmLawedArray', $htmlawed_config);
		$result = $var;
	}

	return $result;
}

elgg_register_event_handler('init','system','minds_init');		

?>
