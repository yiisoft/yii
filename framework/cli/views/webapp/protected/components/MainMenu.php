<?php
/**
 * MainMenu is a widget displaying main menu items.
 *
 * The menu items are displayed as an HTML list. One of the items
 * may be set as active, which could add an "active" CSS class to the rendered item.
 *
 * To use this widget, specify the "items" property with an array of
 * the menu items to be displayed. Each item should be an array with
 * the following elements:
 * - visible: boolean, whether this item is visible;
 * - label: string, label of this menu item. Make sure you HTML-encode it if needed;
 * - url: string|array, the URL that this item leads to. Use a string to
 *   represent a static URL, while an array for constructing a dynamic one.
 * - pattern: array, optional. This is used to determine if the item is active.
 *   The first element refers to the route of the request, while the rest
 *   name-value pairs representing the GET parameters to be matched with.
 */
class MainMenu extends CWidget
{
	public $items=array();

	public function run()
	{
		$items=array();
		$controller=$this->controller;
		$route=$controller->id.'/'.$controller->action->id;
		foreach($this->items as $item)
		{
			if(isset($item['visible']) && !$item['visible'])
				continue;
			$item2=array();
			$item2['label']=$item['label'];
			if(is_array($item['url']))
				$item2['url']=$controller->createUrl($item['url'][0]);
			else
				$item2['url']=$item['url'];
			$pattern=isset($item['pattern'])?$item['pattern']:$item['url'];
			$item2['active']=$this->isActive($route,$pattern);
			$items[]=$item2;
		}
		$this->render('mainMenu',array('items'=>$items));
	}

	protected function isActive($route,$pattern)
	{
		if(!is_array($pattern) || !isset($pattern[0]))
			return false;

		$matched=$pattern[0]===$route;
		if($matched && count($pattern)>1)
		{
			array_splice($pattern,1);
			foreach($pattern as $name=>$value)
			{
				if(!isset($_GET[$name]) || $_GET[$name]!=$value)
					return false;
			}
			return true;
		}
		else
			return $matched;
	}
}