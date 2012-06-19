<?php
/**
 * Extends the core DBObject with the Route table from the Schema on page 69
 *
 * PHP Version: 5.3
 *
 * @category Model
 * @package  OpenTransport
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GPL v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.bcslichfield.com/
 *
 */

namespace Model;

/**
 * This version of the Route schema does not acnowledge PrivateCode or ReversingManoeuveres
 *
 * PHP Version: 5.3
 *
 * @category Model
 * @package  OpenTransport
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GPL v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.bcslichfield.com/
 *
 */
class Route extends DBObject {
	public static function getTable($read=true) {return "route";}
	public static function getPrimaryKey() {return array("id");}
	
	public function addLink(RouteSection $l) {
		return RouteRouteSection::Add($this, $l);
	}
}