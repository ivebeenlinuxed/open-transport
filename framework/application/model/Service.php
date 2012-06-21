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
class Service extends DBObject {
	public static function getTable($read=true) {return "service";}
	public static function getPrimaryKey() {return array("code");}
	
	public function addLine(Line $l) {
		ServiceLine::Add($this, $l);
	}
	
	public function addJourneyPattern(JourneyPattern $jp) {
		ServiceJourneyPattern::Add($this, $jp);
	}
}