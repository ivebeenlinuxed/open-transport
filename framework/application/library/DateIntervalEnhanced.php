<?php
/**
 * Override for DateInterval
 *
 * PHP Version: 5.3
 *
 * @category Library
 * @package  OpenTransport
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GPL v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.bcslichfield.com/
 *
 */

namespace Library;

/**
 * Override for DateInterval
 *
 * PHP Version: 5.3
 *
 * @category Library
 * @package  OpenTransport
 * @author   ivebeenlinuxed <will@bcslichfield.com>
 * @license  GPL v3.0 http://www.gnu.org/licenses/gpl-3.0.txt
 * @link     http://www.bcslichfield.com/
 *
 */
class DateIntervalEnhanced extends \DateInterval
{

	/**
	 * Output DateInterval as seconds
	 *
	 * @return int
	 */
	public function toSeconds()
	{
		return ($this->y * 365 * 24 * 60 * 60) +
		($this->m * 30 * 24 * 60 * 60) +
		($this->d * 24 * 60 * 60) +
		($this->h * 60 *60) +
		($this->i * 60) +
		$this->s;
	}

	/**
	 * Recalculate using the largest unit
	 *
	 * <code>
	 * $di = new DateIntervalEnhanced('PT3600S');
	 * $di->recalculate();
	 * // outputs 1:0:0 instead of 0:0:3600 now!
	 * echo $di->format('%H:%i:%s');
	 * </code>
	 *
	 * @return null
	 */
	public function recalculate()
	{
		$seconds = $this->to_seconds();
		$this->y = floor($seconds/60/60/24/365);
		$seconds -= $this->y * 31536000;
		$this->m = floor($seconds/60/60/24/30);
		$seconds -= $this->m * 2592000;
		$this->d = floor($seconds/60/60/24);
		$seconds -= $this->d * 86400;
		$this->h = floor($seconds/60/60);
		$seconds -= $this->h * 3600;
		$this->i = floor($seconds/60);
		$seconds -= $this->i * 60;
		$this->s = $seconds;
	}
}
?>