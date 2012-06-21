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
class JourneyPatternJourneyPatternSection extends DBObject {
	public static function getTable($read=true) {
		return "journeypattern_section";
	}
	public static function getPrimaryKey() {
		return array("journeypattern", "section");
	}
	
	public static function Add(JourneyPattern $p, JourneyPatternSection $jps) {
		return self::Create(array("journeypattern"=>$p->id, "section"=>$jps->id));
	}
}