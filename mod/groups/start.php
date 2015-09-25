<?php
/**
 * Minds Groups
 */
namespace Minds\plugin\groups;

use Minds\Components;
use Minds\Core;
use Minds\Api;

class start extends Components\Plugin{

	public function __construct(){
		Core\SEO\Manager::add('/groups/profile', function($slugs = array()){
			$guid = $slugs[0];
			$group = new entities\Group($guid);
			if(!$group->name)
				return array();

			return $meta = array(
				'title' => $group->name,
				'description' => $group->briefdescription
			);
		});

		$featured_link = new Core\Navigation\Item();
		$featured_link
			->setPriority(1)
			->setIcon('star')
			->setName('Featured')
			->setTitle('Featured (Groups)')
			->setPath('/groups')
			->setParams(array('filter'=>'featured'));
		$my_link = new Core\Navigation\Item();
		$my_link
			->setPriority(2)
			->setIcon('person_pin')
			->setName('My')
			->setTitle('My (Groups)')
			->setPath('/groups')
			->setParams(array('filter'=>'member'));

		$root_link = new Core\Navigation\Item();
		Core\Navigation\Manager::add($root_link
			->setPriority(7)
			->setIcon('group_work')
			->setName('Groups')
			->setTitle('Groups')
			->setPath('/groups')
			->setParams(array('filter'=>'featured'))
			->addSubItem($featured_link)
			->addSubItem($my_link)
		);

		Api\Routes::add('v1/groups', '\\minds\\plugin\\groups\\api\\v1\\groups');
		Api\Routes::add('v1/groups/group', '\\minds\\plugin\\groups\\api\\v1\\group');
		Api\Routes::add('v1/groups/membership', '\\minds\\plugin\\groups\\api\\v1\\membership');

		\elgg_register_plugin_hook_handler('entities_class_loader', 'all', function($hook, $type, $return, $row){
			if($row->type == 'group')
				return new entities\Group($row);
		});
	}

}
