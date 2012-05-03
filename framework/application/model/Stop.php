<?php
namespace Model;

class Stop extends DBObject {
	public static function getTable($read=true) {return "stop";}
	public static function getPrimaryKey() {return "AtcoCode";}
}