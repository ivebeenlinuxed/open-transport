<?php
namespace Model;

class Route extends DBObject {
	public static function getTable($read=true) {return "route";}
	public static function getPrimaryKey() {return array("id");}
	
	public function addLink(RouteSection $l) {
		return RouteRouteSection::Add($this, $l);
	}
}