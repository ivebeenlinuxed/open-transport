<?php
namespace Model;

class RouteSection extends DBObject {
	public static function getTable($read=true) {return "routesection";}
	public static function getPrimaryKey() {return "id";}
	
	public function addLink(RouteLink $l) {
		RouteSectionRouteLink::Add($this, $l);
	}
}