<?php

namespace Web\Framework\Lib;

/**
 * Class for date related functions
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Date
{
	// @todo: Translation needed
	public static function timeAgo($date)
	{
		$time = strtotime($date);
		$now = time();
		$ago = $now - $time;

		if ($ago < 60)
		{
			$when = round($ago);
			$s = ($when == 1) ? "second" : "seconds";
			return "$when $s ago";
		}
		elseif ($ago < 3600)
		{
			$when = round($ago / 60);
			$m = ($when == 1) ? "minute" : "minutes";
			return "$when $m ago";
		}
		elseif ($ago >= 3600 && $ago < 86400)
		{
			$when = round($ago / 60 / 60);
			$h = ($when == 1) ? "hour" : "hours";
			return "$when $h ago";
		}
		elseif ($ago >= 86400 && $ago < 2629743.83)
		{
			$when = round($ago / 60 / 60 / 24);
			$d = ($when == 1) ? "day" : "days";
			return "$when $d ago";
		}
		elseif ($ago >= 2629743.83 && $ago < 31556926)
		{
			$when = round($ago / 60 / 60 / 24 / 30.4375);
			$m = ($when == 1) ? "month" : "months";
			return "$when $m ago";
		}
		else
		{
			$when = round($ago / 60 / 60 / 24 / 365);
			$y = ($when == 1) ? "year" : "years";
			return "$when $y ago";
		}
	}
}

?>
