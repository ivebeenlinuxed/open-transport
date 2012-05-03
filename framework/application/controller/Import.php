<?php
namespace Controller;

use Model\Route;

use Model\RouteRouteSection;

use Model\RouteLink;

use Model\RouteSection;

use Model\Stop;

class Import {
	public function index() {
		$x = new \XMLReader();
		$x->open(BOILER_LOCATION."../_offline/Admin_Area_360/ATCO_360_TRAIN.txc");
		while ($x->read() !== false) {
			if ($x->nodeType == \XMLReader::ELEMENT) {
				switch ($x->name) {
					case "TransXChange":
						continue 2;
					case "ServicedOrganisations":
						$this->servicedOrganisations($x);
						break;
					case "StopPoints":
						$this->stopPoints($x);
						break;
					case "RouteSections":
						$this->routeSections($x);
						break;
					case "Routes":
						$this->Routes($x);
						break;
					default:
						var_dump($x->name);
						die();
				}
				
			}
		}
	}
	
	private function servicedOrganisations($x) {
		while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "ServicedOrganisations")) {
		}
	}
	
	private function stopPoints($x) {
		while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "StopPoints")) {
			if ($x->nodeType == \XMLReader::ELEMENT && $x->name == "StopPoint") {
				$this->importStop($x->readOuterXML());
			}
		}
	}
	
	private function importStop($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$stop = array();
		$stop['AtcoCode'] = $doc->getElementsByTagName("AtcoCode")->item(0)->nodeValue;
		$stop['Easting'] = $doc->getElementsByTagName("Easting")->item(0)->nodeValue;
		$stop['Northing'] = $doc->getElementsByTagName("Northing")->item(0)->nodeValue;
		$stop['NptgLocalityRef'] = $doc->getElementsByTagName("NptgLocalityRef")->item(0)->nodeValue;
		$stop['CommonName'] = $doc->getElementsByTagName("CommonName")->item(0)->nodeValue;
		$stop['StopType'] = $doc->getElementsByTagName("StopType")->item(0)->nodeValue;
		$stop['AdministrativeAreaRef'] = $doc->getElementsByTagName("AdministrativeAreaRef")->item(0)->nodeValue;
		//FIXME If we have changed, delete and recreate
		if (($s = Stop::Fetch($stop['AtcoCode'])) === false) {
			return Stop::Create($stop);
		}
		return $s;
	}
	
	private function routeSections($x) {
		while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "RouteSections")) {
			if ($x->nodeType == \XMLReader::ELEMENT && $x->name == "RouteSection") {
				$this->importRouteSection($x->readOuterXML());
			}
		}
	}
	
	private function importRouteSection($xml) {
		$doc = new \DOMDocument();
		//$doc->preserveWhiteSpace = false;
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		//var_dump($doc->saveXML());
		$id = array("id"=>$doc->getElementsByTagName("RouteSection")->item(0)->attributes->getNamedItem("id")->nodeValue);
		//FIXME If we have changed, delete and recreate
		if (($section = RouteSection::Fetch($id)) === false) {
			$section = RouteSection::Create($id);
		}
		
		foreach ($xpath->query("//x:RouteLink") as $l) {
			$link = array();
			$link['id'] = $l->attributes->getNamedItem("id")->nodeValue;
			$link['to'] = $xpath->query("x:To/x:StopPointRef", $l)->item(0)->nodeValue;
			$link['from'] = $xpath->query("x:From/x:StopPointRef", $l)->item(0)->nodeValue;
			if (($lo = RouteLink::Fetch($link['id'])) === false) {
				$lo = RouteLink::Create($link);
			}
			$section->addLink($lo);
		}
	}
	
	private function Routes($x) {
	while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "Routes")) {
			if ($x->nodeType == \XMLReader::ELEMENT && $x->name == "Route") {
				$this->importRouteRouteSection($x->readOuterXML());
			}
		}
	}
	
	private function importRouteRouteSection($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		$r = array();
		$r['id'] = $doc->getElementsByTagName("Route")->item(0)->attributes->getNamedItem("id")->nodeValue;
		$r['description'] = $doc->getElementsByTagName("Description")->nodeValue;
		//FIXME If we have changed, delete and recreate
		if (($route = Route::Fetch($r['id'])) === false) {
			$route = Route::Create($r);
		}
	
		foreach ($xpath->query("//x:RouteSectionRef") as $l) {
			$route->addLink(new RouteSection($l->nodeValue));
		}
	}
	
	private function journeyPatternImport($x) {
		
	}
}