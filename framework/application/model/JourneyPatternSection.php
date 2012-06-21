<?php
/**
 * Extends the core DBObject with the JourneyPatternSection
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
 * Extends the core DBObject with the JourneyPatternSection
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
class JourneyPatternSection extends DBObject {
	public static function getTable($read=true) {
		return "journeypatternsection";
	}
	public static function getPrimaryKey() {
		return array("id");
	}
	
	public function addJourneyPatternTimingLink(JourneyPatternTimingLink $l) {
		JourneyPatternSectionJourneyPatternTimingLink::Add($this, $l);
	}
}