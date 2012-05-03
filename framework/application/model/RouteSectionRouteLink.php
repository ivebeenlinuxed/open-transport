<?php
namespace Model;

class RouteSectionRouteLink extends DBObject {
	public static function getTable($read=true) {return "routesection_routelink";}
	public static function getPrimaryKey() {return array("section", "link");}
	
	public static function Add(RouteSection $s, RouteLink $l) {
		$a = array("section"=>$s->id, "link"=>$l->id);
		if (($out = self::Fetch($a)) === false) {
			return self::Create($a);
		}
		return $out;
	}
}