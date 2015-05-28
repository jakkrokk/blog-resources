<?php
/**
 * Create date range from start-date and end-date
 *
 * @param string $start
 * @param string $end（Max Value 2038/1/19）
 * @return  array
 */
function date_range($start,$end) {
	//For return
	$rangeDate = array();

	//Checking values
	if (isset($start) && isset($end)) {
		//Changing Start<>End if start-date is over end-date
		if (strtotime($start) > strtotime($end)) {
			list($start,$end) = array($end,$start);

		}

		//Start date values
		list($startY,$startM) = array(date('Y',strtotime($start)),date('m',strtotime($start)));

		//End date values
		list($endY,$endM) = array(date('Y',strtotime($end)),date('m',strtotime($end)));

		//Start counting from last month
		$d = date('Y-m',strtotime("{$startY}-{$startM} - 1 month"));

		//Loop until end-date
		while ("{$endY}-{$endM}" !== $d = date('Y-m',strtotime("{$d} + 1 month"))) {
			$rangeDate[] = $d;

		}
	}
	return $rangeDate;

}