<?php

namespace App\Library;

use \DateTime;

class DateUtils {

	static function DateToHuman($datetime)
	{
		if( is_null($datetime) )
		{
			return '';
		}
		$now = new DateTime();
		$interval = $now->getTimestamp() - $datetime->getTimestamp();
		//If time < 1 minute
		if( $interval < 60 )
		{
			return "ahora mismo"; //TODO: trasnlate
		}
		//If interval < 1 hour
		elseif( $interval < 3600 )
		{
			return floor($interval / 60) . 'min.';
		}
		//If interval < 1 day
		elseif( $interval < 24 * 3600)
		{
			return floor($interval / 3600) . 'horas y ' . $interval % 3600 . ' min.'; //TODO: translate
		}
		return $datetime->format('j \d\e M \a \l\a\s G:i');
	}

}