<?php
namespace Model;

class RouteRouteSection extends DBObject {
	public static function getTable($read=true) {return "route_routesection";}
	public static function getPrimaryKey() {return array("route", "section");}
	
	public static function Add(Route $s, RouteSection $l) {
		$a = array("route"=>$s->id, "section"=>$l->id);
		if (($out = self::Fetch($a)) === false) {
			return self::Create($a);
		}
		return $out;
	}
}