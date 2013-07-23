<?php
/**
* Minds Archive 
* @package minds.archive
* @author Mark Harding (mark@minds.com)
**/

elgg_register_event_handler('init','system','minds_archive_init', 1);

function minds_archive_init() {

	global $CONFIG;

	elgg_extend_view('page/elements/head', 'archive/meta');
	
	//list featured in sidebar
	elgg_extend_view('page/elements/sidebar', 'archive/featured');
	
	elgg_register_library('archive:kaltura', elgg_get_plugins_path().'archive/vendors/kaltura/api_client/includes.php');
	elgg_register_library('archive:kaltura:editor', elgg_get_plugins_path().'archive/vendors/kaltura/editor/init.php');

	//embed options
	elgg_register_js('kaltura.js', elgg_get_site_url() . 'mod/kaltura_video/kaltura/js/kaltura.js');

    //Loading angularJS
    $angularRoot = elgg_get_site_url() . 'mod/archive/angular/app/';
    $templatesPath = $angularRoot . '/partials';

    $angularSettings = array(
        'templates_path' => $templatesPath
    );

    elgg_register_js(array('angular' => $angularSettings), 'setting');

    // include library
    elgg_register_js('angular.min.js' , $angularRoot . 'lib/angular.min.js');
    elgg_register_js('bootstrap.min.js' , $angularRoot . 'lib/bootstrap/js/bootstrap.min.js');
    elgg_register_js('jquery.ui.widget.js' , $angularRoot . 'lib/jQuery-File-Upload-8.5.0/js/vendor/jquery.ui.widget.js');
    elgg_register_js('jquery.fileupload.js' , $angularRoot . 'lib/jQuery-File-Upload-8.5.0/js/jquery.fileupload.js');
    elgg_register_js('jquery.iframe-transport.js' , $angularRoot . 'lib/jQuery-File-Upload-8.5.0/js/jquery.iframe-transport.js');
//    elgg_register_js('http://player.kaltura.com/mwEmbedLoader.php', 'external');

    // include directives
    elgg_register_js('kaltura-embed.js' , $angularRoot . 'directives/kaltura-embed.js');
    elgg_register_js('kaltura-upload.js' , $angularRoot . 'directives/kaltura-upload.js');
    elgg_register_js('kaltura-thumbnail.js' , $angularRoot . 'directives/kaltura-thumbnail.js');

    // include controllers
    elgg_register_js('UploadController.js' , $angularRoot . 'controllers/UploadController.js');
    elgg_register_js('GalleryController.js' , $angularRoot . 'controllers/GalleryController.js');

    // include services
    elgg_register_js('NodeService.js' , $angularRoot . 'services/NodeService.js');
    elgg_register_js('KalturaService.js' , $angularRoot . 'services/KalturaService.js');
    elgg_register_js('ElggService.js' , $angularRoot . 'services/ElggService.js');

    elgg_register_js('app.js' , $angularRoot . 'app.js');

    // include css
    elgg_register_css('appstyle.css' , $angularRoot .'css/appstyle.css');
    elgg_register_css('bootstrap.min.css' , $angularRoot . 'lib/bootstrap/css/bootstrap.min.css');

    //site menu
	elgg_register_menu_item('site', array(
			'name' => elgg_echo('minds:archive'),
			'href' => elgg_is_logged_in() ? elgg_get_site_url() . "archive/friends/" . elgg_get_logged_in_user_entity()->username : elgg_get_site_url() . 'archive/all',
			'text' => '&#59392;',
			'class' => 'entypo',
			'title' =>  elgg_echo('minds:archive'),
	));
		
	elgg_extend_view('css','archive/css');

	// Register a page handler, so we can have nice URLs (fallback in case some links go to old kaltura_video)
	elgg_register_page_handler('kaltura_video','minds_archive_page_handler');
	elgg_register_page_handler('archive','minds_archive_page_handler');
	elgg_register_page_handler('file','minds_archive_page_handler');

	// Register a url handler
	elgg_register_entity_url_handler('object', 'kaltura_video','minds_archive_entity_url');
	elgg_register_entity_url_handler('object', 'file','minds_archive_entity_url');
	//tidypics is a separate plugin which we plan on integrating into here shortly. Make sure this plugin is below the tidypics plugin
	elgg_register_entity_url_handler('object', 'image','minds_archive_entity_url');
	elgg_register_entity_url_handler('object', 'album','minds_archive_entity_url');
	
	//override icon urls
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', 'minds_archive_file_icon_url_override');	

	// Listen to notification events and supply a more useful message
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'kaltura_notify_message');

	// Add profile widget
    elgg_register_widget_type('kaltura_video',elgg_echo('kalturavideo:label:latest'),elgg_echo('kalturavideo:text:widgetdesc'));

	// Register entity type @todo check if this is needed
	//elgg_register_entity_type('object','kaltura_video');
	
	// register actions
	$action_path = elgg_get_plugins_path() . 'archive/actions/';

	//actions for the plugin
	elgg_register_action("archive/delete", $action_path . "delete.php");//new (studio)
	elgg_register_action("archive/download", $action_path . "download.php");
	elgg_register_action("archive/monetize", $action_path . "monetize.php");
	elgg_register_action("archive/feature", $action_path . "feature.php");
	elgg_register_action("archive/save", $action_path . "save.php");
	elgg_register_action("archive/add_album", $action_path . "tidypics/add_album.php");
	elgg_register_action("archive/upload", $action_path . "upload.php");
    elgg_register_action("archive/addElggVideo", $action_path . "addAngular.php");
    elgg_register_action("archive/deleteElggVideo" , $action_path . "deleteAngular.php");
    elgg_register_action("archive/selectAlbum" , $action_path . "tidypics/album.php");
    elgg_register_action("archive/getKSession" , $action_path . "generateKalturaSession.php");

	//Setup kaltura
	
	elgg_register_event_handler('pagesetup','system','minds_archive_page_setup');
}

function minds_archive_entity_url($entity) {
		global $CONFIG;
		$title = str_replace(" ", "-", $entity->title);
		$title = preg_replace('/\.[^.]*$/', '', $title);
		return elgg_get_site_url() . "archive/view/" . $entity->getGUID() . "/" . $title;
}


function minds_archive_page_setup() {
	global $CONFIG;

	$page_owner = elgg_get_page_owner_entity();
	$user = elgg_get_logged_in_user_entity();
	
	if (elgg_get_context() == 'archive') {
		
		elgg_register_menu_item('page', array(
			'name' => elgg_echo('upload'),
			'href' => elgg_get_site_url() . 'archive/upload',
			'text' => elgg_echo('minds:archive:upload'),
			'class' => 'pagesactions elgg-lightbox',
			'priority' => 0,
			'section'=>'actions'
		));

        elgg_register_menu_item('page', array(
			'name' =>elgg_echo('minds:archive:all'),
			'href' => elgg_get_site_url() . "archive/all",
			'text' =>  elgg_echo('minds:archive:all'),
			'priority' => 400,
			'section' => 'menu-a'
		));
			
		elgg_register_menu_item('page', array(
			'name' => elgg_echo('minds:archive:top'),
			'href' => elgg_get_site_url() . "archive/top",
			'text' =>  elgg_echo('minds:archive:top'),
			'priority' => 500,
			'section' => 'menu-a'
		));
		
		elgg_register_menu_item('page', array(
			'name' => elgg_echo('minds:archive:featured'),
			'href' => elgg_get_site_url() . "archive/featured",
			'text' =>  elgg_echo('minds:archive:featured'),
			'priority' => 600,
			'section' => 'menu-a'
		));
			
		if (($page_owner == $user || !$page_owner) && elgg_is_logged_in()) {

			elgg_register_menu_item('page', array(
				'name' => elgg_echo('minds:archive:mine'),
				'href' =>  elgg_get_site_url() ."archive/" . $user->username,
				'text' =>  elgg_echo('minds:archive:mine'),
			));

			elgg_register_menu_item('page', array(
				'name' => elgg_echo('minds:archive:network'),
				'href' => elgg_get_site_url() ."archive/network/" . $user->username,
				'text' =>  elgg_echo('minds:archive:network'),
			));

		} elseif ($page_owner) {
			
			elgg_register_menu_item('page', array(
				'name' => elgg_echo('minds:archive:owner', array($page_owner->name)),
				'href' => elgg_get_site_url() . "archive/" . $page_owner->username,
				'text' => elgg_echo('minds:archive:owner', array($page_owner->name)),
			));
		
			if ($page_owner instanceof ElggUser) { // Sorry groups, this isn't for you.
				elgg_register_menu_item('page', array(
					'name' => elgg_echo('minds:archive:owner:network', array($page_owner->name)),
					'href' => elgg_get_site_url() ."archive/" . $page_owner->username ."/network/",
					'text' => elgg_echo('minds:archive:owner:network', array($page_owner->name)),
				));
			}
			
		} 
		
	}
	
	// Group submenu option
	if ($page_owner instanceof ElggGroup && elgg_get_context() == 'groups') {
		if($page_owner->kaltura_video_enable != "no") {
			elgg_register_menu_item('page', array(
					'name' =>sprintf(elgg_echo('kalturavideo:label:groupvideos'),$page_owner->name),
					'href' => $CONFIG->wwwroot . "archive/" . $page_owner->username,
					'text' =>  sprintf(elgg_echo('kalturavideo:label:groupvideos'),$page_owner->name),
				));
		}
	}
	
	/**
	 * EMBED 
	 */
	// embed support
	$item = ElggMenuItem::factory(array(
		'name' => 'file',
		'text' => elgg_echo('file'),
		'priority' => 12,
		'data' => array(
			'options' => array(
				'type' => 'object',
				'subtype' => 'file',
			),
		),
	));
	elgg_register_menu_item('embed', $item);

	 // embed support
        $item = ElggMenuItem::factory(array(
                'name' => 'image',
                'text' => elgg_echo('image'),
                'priority' => 11,
                'data' => array(
                        'options' => array(
                                'type' => 'object',
                                'subtype' => 'image',
                        ),
                ),
        ));
        elgg_register_menu_item('embed', $item);

        // embed support
        $item = ElggMenuItem::factory(array(
                'name' => 'video',
                'text' => elgg_echo('kalturavideo:label:videoaudio'),
                'priority' => 10,
                'data' => array(
                        'options' => array(
                                'type' => 'object',
                                'subtype' => 'kaltura_video',
                        ),
                ),
        ));
        elgg_register_menu_item('embed', $item);

	$item = ElggMenuItem::factory(array(
		'name' => 'file_upload',
		'text' => elgg_echo('minds:archive:upload'),
		'priority' => 100,
		'data' => array(
			'view' => 'archive/embed_upload',
		),
	));

	elgg_register_menu_item('embed', $item);
}

function minds_archive_page_handler($page) {
		
	global $CONFIG;
	
	elgg_load_library('archive:kaltura');

	if(!elgg_get_plugin_setting("kaltura_server_url","archive")){
		// If the URL is just 'feeds/username', or just 'feeds/', load the standard feeds index
		include(dirname(__FILE__) . "/missconfigured.php");
		return true;
	}
	
	switch($page[0]) {
		case 'all':
			include('pages/archive/all.php');
			break;
		case 'top':
			include('pages/archive/top.php');
			break;
		case 'featured':
			include('pages/archive/featured.php');
			break;	
		case 'wall':
			$tab = $page[1] ? $page[1] : 'featured';
			set_input('tab', $tab);
			include('pages/archive/wall.php');
			break;
		case 'api_upload':
			include('pages/archive/api_upload.php');
			break;
		case 'upload':			
			switch($page[1]) {
				case 'videoaudio':
					include('pages/archive/kaltura_upload.php');
					break;
				case 'others':
					include('pages/archive/others_upload.php');
					break;
				case 'album':
					if($page[2] == 'create'){
						include('pages/archive/add_album.php');
						return true;
					}
					set_input('guid',$page[2]);
					include(elgg_get_plugins_path().'tidypics/pages/photos/image/upload.php');
					break;
				case 'batch':
					set_input('guid',$page[2]);
					include(elgg_get_plugins_path().'tidypics/pages/photos/batch/edit.php');
					break;
                case 'angularJS':
                    include('pages/archive/angularJS_upload.php');
                    break;
				default:
					include('pages/archive/upload.php');
			}
			return true;
			break;
		case 'kaltura':
			switch($page[1]){
				case 'ajax-update':
					set_input('uploaded_entry_id', $page[2]);
					include('pages/archive/kaltura/ajax-update.php');
					break;
				default:
					return false;
			}
			break;
        case 'angular':
            return;
            break;
		case 'show':
		case 'view':
			set_input('guid',$page[1]);
			include('pages/archive/view.php');
			return true;			
			break;
		case 'inline':
			set_input('video_id',$page[1]);
			include('pages/archive/uiVideoInline.php');
			return true;
			break;
		case 'edit':
			set_input('guid',$page[1]);
			set_input('entryid',$page[1]);
			include('pages/archive/edit.php');
			break;
		case 'owner':
			set_input('username', $page[1]);
			include(dirname(__FILE__) . "/pages/archive/owner.php");
			break;
		case 'friends':
		case 'network':
			set_input('username', $page[1]);
			include(dirname(__FILE__) . "/pages/archive/network.php");
			break;	
		case 'unavailable':
			include(dirname(__FILE__) . "/pages/archive/unavailable.php");
			break;
		default:
			set_input('username',$page[0]);
			$user = get_user_by_username($page[0]);
			elgg_set_page_owner_guid($user->guid);
			if (isset($page[0])) {
				switch($page[1]) {
					case 'network':
						include(dirname(__FILE__) . "/pages/archive/network.php");
						break;
					case 'show':
					case 'view':
						set_input('videopost',$page[2]);
						include(dirname(__FILE__) . "/pages/archive/show.php");	
						break;
					default:
						include(dirname(__FILE__) . "/pages/archive/owner.php");
				}
			} else {
				include('pages/archive/all.php');
			}
	}

	return true;
}

//elgg_register_event_handler('upgrade','system','kaltura_setup_init');
/* Setup Kaltura to work with elgg 
 * This is run along side init
 */
function kaltura_setup_init(){
	
	elgg_load_library('archive:kaltura');
	
	$email = elgg_get_plugin_setting('email', 'archive');
	$partnerId = elgg_get_plugin_setting('partner_id', 'archive');
	$password = elgg_get_plugin_setting('password', 'archive');
	
	if ($partnerId && $email && $password) {
		
		$partner = new KalturaPartner();
			//Setup the Kaltura credentials
			try {
				$kmodel = KalturaModel::getInstance();
				$partner = $kmodel->getSecrets($partnerId, $email, $password);

				$partnerId = $partner->id;
				$subPartnerId = $partnerId * 100;
				$secret = $partner->secret;
				$adminSecret = $partner->adminSecret;
				$cmsUser = $partner->adminEmail;

				//Register Elgg vars
				elgg_set_plugin_setting("user",$cmsUser,"archive");
				elgg_set_plugin_setting("password",$password,"archive");
				elgg_set_plugin_setting("subp_id", $subPartnerId,"archive");
				elgg_set_plugin_setting("secret", $secret,"archive");
				elgg_set_plugin_setting("admin_secret", $adminSecret,"archive");

				system_message(elgg_echo("kalturavideo:registeredok"));
			}
			catch(Exception $e) {
				var_dump($e);
				exit;
				system_message('failed');
			}
	}
}

/**
  * KALTURA CONVERT CRON SCRIPT
  *
  */
function minds_archive_kalturavideo_convert($hook, $entity_type, $returnvalue, $params){
	login(get_user_by_username('cron'));
	require_once("kaltura/api_client/includes.php");
    //grab list of videos that are not converted.
	$videos = elgg_get_entities_from_metadata( array(
					'type' => 'object',
					'subtype' => 'kaltura_video',
					'limit' => 0,
					//'metadata_name_value_pairs' => array( 'name' => 'converted', value => 'value', 'operand' => '=', 'case_sensitive' => TRUE )
			));
	$kmodel = KalturaModel::getInstance();
	
	$c = 0;
	foreach($videos as $video){
		try{
			/*$mediaEntries = $kmodel->listMixMediaEntries($video->kaltura_video_id);
			$mediaEntry = $mediaEntries[0];*/
			$mediaEntry = $kmodel->getEntry($video->uploaded_id);
			if((!$video->converted || $video->converted != true) && $mediaEntry->status == 2){
				//limit to 20 per minute
				if(++$c > 20){ break; }
				
				$entry = $kmodel->appendMediaToMix($video->kaltura_video_id, $mediaEntry->id);
				
				try {
					$kmodel = KalturaModel::getInstance();
					$mixEntry = new KalturaMixEntry();
					$mixEntry->name = $entry->name;
					$mixEntry->description = $entry->description;
					$mixEntry->tags = $entry->tags;
					$mixEntry->adminTags = KALTURA_ADMIN_TAGS;
					$entry = $kmodel->updateMixEntry($video_id,$mixEntry);
				} catch(Exception $e) {
					$error = $e->getMessage();
				}
				
				$video->converted = true;			
				if($video->save()){
					$resulttext = elgg_echo("kalturavideo:cron:converted:video") . $video->kaltura_video_id;
					//add to the river
					add_to_river('river/object/kaltura_video/update','update',$video->getOwnerGUID(),$video->getGUID());
					
					//increment the owners quota
					$assets = $kmodel->getflavourAssets($video->uploaded_id);
					$asset_vars = get_object_vars($assets[0]);
					$user = get_entity($video->getOwnerGUID());
					$user->quota_storage = $user->quota_storage + ($asset_vars['size']*1024) ;
					
					$user->save;
			
				}
				
			} 
		} catch (Exception $e){
			
		}
	}
	//logout for secruity!
	logout(); 
    return $resulttext;
  }

/**
 * Returns an overall file type from the mimetype
 *
 * @param string $mimetype The MIME type
 * @return string The overall type
 */
function file_get_simple_type($mimetype) {

	switch ($mimetype) {
		case "application/msword":
		case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
			return "document";
			break;
		case "application/pdf":
			return "document";
			break;
		case "application/ogg":
			return "audio";
			break;
	}

	if (substr_count($mimetype, 'text/')) {
		return "document";
	}

	if (substr_count($mimetype, 'audio/')) {
		return "audio";
	}

	if (substr_count($mimetype, 'image/')) {
		return "image";
	}

	if (substr_count($mimetype, 'video/')) {
		return "video";
	}

	if (substr_count($mimetype, 'opendocument')) {
		return "document";
	}

	return "general";
}

/**
 * Override the default entity icon for files
 *
 * Plugins can override or extend the icons using the plugin hook: 'file:icon:url', 'override'
 *
 * @return string Relative URL
 */
function minds_archive_file_icon_url_override($hook, $type, $returnvalue, $params) {
	global $CONFIG;
	$entity = $params['entity'];
	$file = $entity;
	$size = $params['size'];
	if (elgg_instanceof($file, 'object', 'file')) {

		// thumbnails get first priority
		if ($file->thumbnail) {
			$ts = (int)$file->icontime;
			return $CONFIG->cdn_url .  "mod/archive/thumbnail.php?file_guid=$file->guid&size=$size&icontime=$ts";
		}

		$mapping = array(
			'application/excel' => 'excel',
			'application/msword' => 'word',
			'application/ogg' => 'music',
			'application/pdf' => 'pdf',
			'application/powerpoint' => 'ppt',
			'application/vnd.ms-excel' => 'excel',
			'application/vnd.ms-powerpoint' => 'ppt',
			'application/vnd.oasis.opendocument.text' => 'openoffice',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'ppt',
			'application/x-gzip' => 'archive',
			'application/x-rar-compressed' => 'archive',
			'application/x-stuffit' => 'archive',
			'application/zip' => 'archive',

			'text/directory' => 'vcard',
			'text/v-card' => 'vcard',

			'application' => 'application',
			'audio' => 'music',
			'text' => 'text',
			'video' => 'video',
		);

		$mime = $file->mimetype;
		if ($mime) {
			$base_type = substr($mime, 0, strpos($mime, '/'));
		} else {
			$mime = 'none';
			$base_type = 'none';
		}

		if (isset($mapping[$mime])) {
			$type = $mapping[$mime];
		} elseif (isset($mapping[$base_type])) {
			$type = $mapping[$base_type];
		} else {
			$type = 'general';
		}

		if ($size == 'large') {
			$ext = '_lrg';
		} else {
			$ext = '';
		}
		
		$url = $CONFIG->cdn_url . "mod/archive/graphics/icons/{$type}{$ext}.gif";
		$url = elgg_trigger_plugin_hook('file:icon:url', 'override', $params, $url);
		return $url;
	} elseif(elgg_instanceof($entity, 'object', 'kaltura_video')) {
	
		return kaltura_get_thumnail($entity->kaltura_video_id, 120,68, 100);
	}
}


?>
