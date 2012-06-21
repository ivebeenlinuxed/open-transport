<?php
/**
 * Extends the core DBObject with the Service_JourneyPattern, used to link multiple-to-multiple: Service <-> JourneyPattern
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
 * Extends the core DBObject with the Service_JourneyPattern, used to link multiple-to-multiple: Service <-> JourneyPattern
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
class ServiceJourneyPattern extends DBObject {
	public static function getTable($read=true) {
		return "service_journeypattern";
	}
	public static function getPrimaryKey() {
		return array("service", "journeypattern");
	}
	
	public static function Add(Service $p, JourneyPattern $jp) {
		return self::Create(array("service"=>$p->code, "journeypattern"=>$jp->id));
	}
}