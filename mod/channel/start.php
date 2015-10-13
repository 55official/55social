<?php
/**
 * Minds Channel Profiles
 *
 * @package channel
 */
namespace minds\plugin\channel;

use Minds\Core;
use Minds\Helpers;
use Minds\Entities;
use Minds\Api;

class start extends \ElggPlugin{

	/**
	 * Init function
	 */
	public function init(){

		Core\Config::build()->minusername = 2;

		Core\SEO\Manager::add('/', function($slugs = array()){

			if(isset($slugs[0]) && is_string($slugs[0])){
				$user = new Entities\User($slugs[0]);
				if(!$user->guid)
					return array();

				return $meta = array(
					'title' => $user->name,
					'description' => "Subscribe to @$user->username on Minds. " . strip_tags($user->briefdescription)
				);
			}

		});

	  Api\Routes::add('v1/channel', "\\minds\\plugin\\channel\\api\\v1\\channel");
		\Minds\Core\Events\Dispatcher::register('export:extender', 'all', function($event){
		    $params = $event->getParameters();
		    $export = array();
		    if($params['entity']->ownerObj && is_array($params['entity']->ownerObj)){
			    //$export['ownerObj'] = \Minds\Helpers\Export::sanitize($params['entity']->ownerObj);
			  //  $export['ownerObj']['guid'] = (string) $params['entity']->ownerObj['guid'];
		      $event->setResponse($export);
				}
		});

		/**
		 * Returns the url.. this should really be in models/entities now
		 */
		elgg_register_entity_url_handler('user', 'all', function($user){
			//if($user->base_node)
			//	return $user->base_node. $user->username;
			//else
				return elgg_get_site_url() . $user->username;
		});

		//set a new file size
		elgg_set_config('icon_sizes', array(
			'topbar' => array('w'=>16, 'h'=>16, 'square'=>TRUE, 'upscale'=>TRUE),
			'tiny' => array('w'=>25, 'h'=>25, 'square'=>TRUE, 'upscale'=>TRUE),
			'small' => array('w'=>40, 'h'=>40, 'square'=>TRUE, 'upscale'=>TRUE),
			'medium' => array('w'=>100, 'h'=>100, 'square'=>TRUE, 'upscale'=>TRUE),
			'large' => array('w'=>425, 'h'=>425, 'square'=>FALSE, 'upscale'=>FALSE),
			//'xlarge'=> array('w'=>400, 'h'=>400, 'square'=>false, 'upscale'=>false),
			'master' => array('w'=>550, 'h'=>550, 'square'=>FALSE, 'upscale'=>FALSE),
		));

	}

}
