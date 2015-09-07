<?php
/**
 * Blogs
 */

namespace minds\plugin\blog;

use Minds\Components;
use Minds\Core;
use Minds\Api;

class start extends Components\Plugin{

	public function __construct(){

		Api\Routes::add('v1/blog', '\\minds\\plugin\\blog\\api\\v1\\blog');

		//@todo update this to OOP
		\elgg_register_plugin_hook_handler('entities_class_loader', 'all', function($hook, $type, $return, $row){
			if($row->type == 'object' && $row->subtype == 'blog')
				return new entities\Blog($row);
		});

		$featured_link = new Core\Navigation\Item();
		$featured_link
			->setPriority(1)
			->setIcon('star')
			->setName('Featured')
			->setTitle('Featured (Blogs)')
			->setPath('/blog')
			->setParams(array('filter'=>'featured'));
		$trending_link = new Core\Navigation\Item();
		$trending_link
			->setPriority(2)
			->setIcon('trending_up')
			->setName('Trending')
			->setTitle('Trending (Blogs)')
			->setPath('/blog')
			->setParams(array('filter'=>'trending'));
		$my_link = new Core\Navigation\Item();
		$my_link
			->setPriority(3)
			->setIcon('person_pin')
			->setName('My')
			->setTitle('My (Blogs)')
			->setPath('/blog')
			->setParams(array('filter'=>'owner'));

		$link = new Core\Navigation\Item();
		Core\Navigation\Manager::add($link
			->setPriority(4)
			->setIcon('description')
			->setName('Blogs')
			->setTitle('Blogs')
			->setPath('/blog')
			->setParams(array('filter'=>'featured'))
			->addSubItem($featured_link)
			->addSubItem($trending_link)
			->addSubItem($my_link)
		);

	}
}
