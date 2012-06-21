<?php
/**
 * Extends the core DBObject with the JourneyPattern table
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
 * Extends the core DBObject with the JourneyPattern table
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
class JourneyPattern extends DBObject {
	public static function getTable($read=true) {return "journeypattern";}
	public static function getPrimaryKey() {return array("id");}
	
	
	
	public function addJourneyPatternSection(JourneyPatternSection $s) {
		JourneyPatternJourneyPatternSection::Add($this, $s);
	}
}