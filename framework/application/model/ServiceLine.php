<?php
/**
 * Extends the core DBObject with the LineService table - Multiple-to-Multiple link: Line <-> Service
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
 * Extends the core DBObject with the LineService table - Multiple-to-Multiple link: Line <-> Service
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
class ServiceLine extends DBObject {
	public static function getTable($read=true) {return "service_line";}
	public static function getPrimaryKey() {return array("service", "line");}
	
	public static function Add(Service $s, Line $l) {
		return self::Create(array("service"=>$s->code, "line"=>$l->id));
	}
}