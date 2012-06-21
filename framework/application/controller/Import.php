<?php
namespace Controller;

use Model\JourneyPatternSection;

use Model\JourneyPatternSectionJourneyPatternTimingLink;

use Model\Service;

use Model\Line;

use Model\JourneyPattern;

use Model\Operator;

use Model\JourneyPatternTimingLink;

use Model\Route;

use Model\RouteRouteSection;

use Model\RouteLink;

use Model\RouteSection;

use Model\Stop;

class Import {
	/**
	 * If true table truncates first
	 * 
	 * @var bool
	 */
	private $suicidal = true;
	
	public function index() {
		if ($this->suicidal) {
			Service::Truncate();
			JourneyPattern::Truncate();
			StopPoints::Truncate();
		}
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
					case "JourneyPatternSections":
						$this->journeyPatternSections($x);
						break;
					case "Operators":
						$this->Operators($x);
						break;
					case "Services":
						$this->Services($x);
						break;
					default:
						echo "UNKNOWN: ";
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
	
	/**
	 * Imports a route section
	 * 
	 * @param \XMLReader $x The current XML Reader at position of start tag
	 * 
	 * @return null
	 */
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
	
	/**
	 * Imports a route section
	 * 
	 * @param string $xml
	 */
	private function importRouteRouteSection($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		$r = array();
		$r['id'] = $doc->getElementsByTagName("Route")->item(0)->attributes->getNamedItem("id")->nodeValue;
		//FIXME does not import description
		if ($doc->getElementsByTagName("Description")->length > 0) {
			$r['description'] = $doc->getElementsByTagName("Description")->item(0)->nodeValue;
		}
		//FIXME If we have changed, delete and recreate
		if (($route = Route::Fetch($r['id'])) === false) {
			$route = Route::Create($r);
		}

		foreach ($xpath->query("//x:RouteSectionRef") as $l) {
			$route->addLink(new RouteSection($l->nodeValue));
		}
	}
	
	/**
	 * Imports all the JourneyPatternSection elements
	 * 
	 * The timing links are grouped using a JourneyPatternSection, allowing the reuse of whole sequences of links in different patterns.
	 * 
	 * @param \XMLReader $x The current XML Reader at position of start tag
	 * 
	 * @return null
	 */
	private function journeyPatternSections($x) {
		while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "JourneyPatternSections")) {
			if ($x->nodeType == \XMLReader::ELEMENT && $x->name == "JourneyPatternSection") {
				$this->importJourneyPatternSection($x->readOuterXml());
			}
		}
	}
	
	/**
	 * Imports one JourneyPatternSection
	 * 
	 * @param string $xml Outer XML String
	 * 
	 * @return null
	 */
	private function importJourneyPatternSection($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		$jp = array();
		$jp['id'] = $doc->documentElement->attributes->getNamedItem("id")->nodeValue;
		if (($jps = JourneyPatternSection::Fetch($jp['id'])) !== false) {
			return $jps;
		}
		$jps = JourneyPatternSection::Create($jp);
		foreach ($doc->getElementsByTagName("JourneyPatternTimingLink") as $link) {
			$jps->addJourneyPatternTimingLink($this->importJourneyPatternTimingLink($doc->saveXML($link)));
		}
		return $jps;
	}
	
	/**
	 * Imports JourneyPattenTimingLink element
	 * 
	 * @param string $xml Outer XML String
	 * 
	 * @return \Model\JourneyPatternTimingLink
	 */
	private function importJourneyPatternTimingLink($xml) {
		//echo $xml;
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		
		//echo $xml;
		$link = array();
		$link['id'] = $doc->getElementsByTagName("JourneyPatternTimingLink")->item(0)->attributes->getNamedItem("id")->nodeValue;
		//$link['to'] = $xpath->query("x:To/x:StopPointRef")->item(0)->nodeValue;
		//$link['from'] = $xpath->query("x:From/x:StopPointRef")->item(0)->nodeValue;
		if ($xpath->query("To/WaitTime")->length > 0) {
			$wt = new \Library\DateIntervalEnhanced($xpath->query("To/WaitTime")->item(0)->nodeValue);
			$link['wait_time'] = $wt->toSeconds();
		}
		if ($xpath->query("To/Activity")->length > 0) {
			$link['activity'] = $xpath->query("To/Activity")->item(0)->nodeValue;
		}
		$link['timing_status'] = $xpath->query("To/TimingStatus")->item(0)->nodeValue;
		$rt = new \Library\DateIntervalEnhanced($xpath->query("RunTime")->item(0)->nodeValue);
		$link['run_time'] = $rt->toSeconds();
		$link['route_link'] = $xpath->query("RouteLinkRef")->item(0)->nodeValue;
		
		if (($j = JourneyPatternTimingLink::Fetch($link['id'])) !== false) {
			return $j;
		}
		return JourneyPatternTimingLink::Create($link);
	}
	
	/**
	 * Import <Operators> tag
	 * 
	 * @param \XMLReader $x The current XML Reader at position of start tag
	 * 
	 * @return null
	 */
	private function Operators($x) {
		while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "Operators")) {
			if ($x->nodeType == \XMLReader::ELEMENT && $x->name == "Operator") {
				$this->importOperator($x->readOuterXML());
			}
		}
	}
	
	/**
	 * Import a single operator
	 * 
	 * @param string $xml Outer XML String
	 * 
	 * @return null
	 */
	private function importOperator($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		
		
		
		$operator = array();
		$operator['code'] = $doc->getElementsByTagName("OperatorCode")->item(0)->nodeValue;
		$operator['name'] = $doc->getElementsByTagName("OperatorShortName")->item(0)->nodeValue;
		if (($j = Operator::Fetch($operator['code'])) !== false) {
			return $j;
		}
		return Operator::Create($operator);
	}
	
	
	/**
	 * Import <Services> tag
	 *
	 * @param \XMLReader $x The current XML Reader at position of start tag
	 *
	 * @return null
	 */
	private function Services($x) {
		while ($x->read() && !($x->nodeType == \XMLReader::END_ELEMENT && $x->name == "Services")) {
			if ($x->nodeType == \XMLReader::ELEMENT && $x->name == "Service") {
				$this->importService($x->readOuterXML());
			}
		}
	}
	
	/**
	 * Import a single service
	 *
	 * @param string $xml Outer XML String
	 *
	 * @return null
	 */
	private function importService($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		$service = array();
		$service['code'] = $xpath->query("x:ServiceCode")->item(0)->nodeValue;
		$service['start_date'] = strtotime($xpath->query("x:OperatingPeriod/x:StartDate")->item(0)->nodeValue);
		$service['end_date'] = strtotime($xpath->query("x:OperatingPeriod/x:EndDate")->item(0)->nodeValue);
		$service['operator'] = $xpath->query("x:RegisteredOperatorRef")->item(0)->nodeValue;
		$service['origin'] = $xpath->query("x:StandardService/x:Origin")->item(0)->nodeValue;
		$service['destination'] = $xpath->query("x:StandardService/x:Destination")->item(0)->nodeValue;
		if (Service::Exists($service['code'])) {
			return;
		}
		$s = Service::Create($service);
		foreach ($xpath->query("x:StandardService/x:JourneyPattern") as $jp) {
			$s->addJourneyPattern($this->importJourneyPattern($doc->saveXML($jp)));
		}
		
		
		foreach ($xpath->query("x:Lines/x:Line") as $node) {
			$line = array("id"=>$node->attributes->getNamedItem("id")->nodeValue, "name"=>$node->getElementsByTagName("LineName")->item(0)->nodeValue);
			if (($l = Line::Fetch($line['id'])) === false) {
				$l = Line::Create($line);
			}
			$s->addLine($l);
		}
	}
	
	/**
	 * Import a JourneyPattern
	 *
	 * @param string $xml Outer XML String
	 *
	 * @return \Model\JourneyPattern
	 */
	private function importJourneyPattern($xml) {
		$doc = new \DOMDocument();
		$doc->loadXML($xml);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('x', "http://www.transxchange.org.uk/");
		
		$jp = array();
		$jp['id'] = $doc->documentElement->attributes->getNamedItem("id")->nodeValue;
		$jp['destination_display'] = $doc->getElementsByTagName("DestinationDisplay")->item(0)->nodeValue;
		$jp['direction'] = $doc->getElementsByTagName("Direction")->item(0)->nodeValue;
		$jp['route'] = $doc->getElementsByTagName("RouteRef")->item(0)->nodeValue;
		if (($j = JourneyPattern::Fetch($jp['id'])) !== false) {
			return $j;
		}
		
		$j = JourneyPattern::Create($jp);
		foreach ($doc->getElementsByTagName("JourneyPatternSectionRefs") as $node) {
			$j->addJourneyPatternSection(new JourneyPatternSection($node->nodeValue));
		}
		return $j;
		
	}
}