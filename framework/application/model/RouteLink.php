<?php
namespace Model;

class RouteLink extends DBObject {
	public static function getTable($read=true) {return "routelink";}
	public static function getPrimaryKey() {return "id";}
}