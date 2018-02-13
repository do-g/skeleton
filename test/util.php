<?php

function test_count_week_days() {
	$days = [
		1 => ['Monday',    2],
		2 => ['Tuesday',   3],
		3 => ['Wednesday', 4],
		4 => ['Thursday',  5],
		5 => ['Friday',    6],
		6 => ['Saturday',  7],
		0 => ['Sunday',    8],
	];
	echo "<table cellpadding='10'>";
	foreach ($days as $i => $day) {
		echo "<tr><td colspan='4'><b>Counting {$day[0]}s ({$i}) starting from January {$day[1]}</b></td></tr>";
		for ($k = 0; $k < 7; $k++) {
			$from_day = $day[1] + $k;
			$from_pad = str_pad($from_day, 2, 0, STR_PAD_LEFT);
			$from_date = new DateTime("2018-01-{$from_pad}");
			for ($j = 0; $j <= 28; $j++) {
				$to_date = clone $from_date;
				$to_date->modify("+{$j} days");
				$days = $j + 1;
				$count = Util::count_week_days($from_date, $days, $i);
				$weight = $to_date->format('w') == $i ? 'bold' : 'normal';
				echo "<tr><td>{$from_date->format('l')} {$from_date->format('j')}</td><td style='font-weight: {$weight}'>{$to_date->format('l')} {$to_date->format('j')}</td><td>({$days} days)</td><td style='font-weight: {$weight}'>{$count}</td></tr>";
			}
			echo "<tr><td colspan='4'>&nbsp;</td></tr>";
		}
	}
	echo "</table>";
}

function test_count_free_days() {
	$start_days = range(1, 7);
	echo "<table cellpadding='10'>";
	foreach ($start_days as $i => $day) {
		echo "<tr><td colspan='4'><b>Counting WEEKEND DAYs starting from January {$day}</b></td></tr>";
		$from_pad = str_pad($day, 2, 0, STR_PAD_LEFT);
		$from_date = new DateTime("2018-01-{$from_pad}");
		for ($j = 0; $j <= 28; $j++) {
			$to_date = clone $from_date;
			$to_date->modify("+{$j} days");
			$days = $j + 1;
			$count = Util::count_free_days($from_date, $days);
			$weight = in_array($to_date->format('w'), [6, 0]) ? 'bold' : 'normal';
			echo "<tr><td>{$from_date->format('l')} {$from_date->format('j')}</td><td style='font-weight: {$weight}'>{$to_date->format('l')} {$to_date->format('j')}</td><td>({$days} days)</td><td style='font-weight: {$weight}'>{$count}</td></tr>";
		}
		echo "<tr><td colspan='4'>&nbsp;</td></tr>";
	}
	echo "</table>";
	echo "<p>****************************************************************</p>";
	echo "<table cellpadding='10'>";
	foreach ($start_days as $i => $day) {
		echo "<tr><td colspan='4'><b>Counting SATURDAYs starting from January {$day}</b></td></tr>";
		$from_pad = str_pad($day, 2, 0, STR_PAD_LEFT);
		$from_date = new DateTime("2018-01-{$from_pad}");
		for ($j = 0; $j <= 28; $j++) {
			$to_date = clone $from_date;
			$to_date->modify("+{$j} days");
			$days = $j + 1;
			$count = Util::count_free_days($from_date, $days, 6);
			$weight = in_array($to_date->format('w'), [6]) ? 'bold' : 'normal';
			echo "<tr><td>{$from_date->format('l')} {$from_date->format('j')}</td><td style='font-weight: {$weight}'>{$to_date->format('l')} {$to_date->format('j')}</td><td>({$days} days)</td><td style='font-weight: {$weight}'>{$count}</td></tr>";
		}
		echo "<tr><td colspan='4'>&nbsp;</td></tr>";
	}
	echo "</table>";
	echo "<p>****************************************************************</p>";
	echo "<table cellpadding='10'>";
	foreach ($start_days as $i => $day) {
		echo "<tr><td colspan='4'><b>Counting SUNDAYs starting from January {$day}</b></td></tr>";
		$from_pad = str_pad($day, 2, 0, STR_PAD_LEFT);
		$from_date = new DateTime("2018-01-{$from_pad}");
		for ($j = 0; $j <= 28; $j++) {
			$to_date = clone $from_date;
			$to_date->modify("+{$j} days");
			$days = $j + 1;
			$count = Util::count_free_days($from_date, $days, 0);
			$weight = in_array($to_date->format('w'), [0]) ? 'bold' : 'normal';
			echo "<tr><td>{$from_date->format('l')} {$from_date->format('j')}</td><td style='font-weight: {$weight}'>{$to_date->format('l')} {$to_date->format('j')}</td><td>({$days} days)</td><td style='font-weight: {$weight}'>{$count}</td></tr>";
		}
		echo "<tr><td colspan='4'>&nbsp;</td></tr>";
	}
	echo "</table>";
}

function test_count_days() {
	$start = new DateTime('2017-12-01');
	echo "<table cellpadding='10'>";
	for ($i = 0; $i < 70; $i++) {
		$end = clone $start;
		$end->modify("+{$i} days");
		$count = Util::count_days($start, $end);
		echo "<tr><td>{$start->format('Y-m-d D')}</td><td>{$end->format('Y-m-d D')}</td><td>{$count}</td></tr>";
	}
	echo "</table>";
}

function test_count_work_days() {
	$start = new DateTime('2017-12-01');
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='3'><b>Counting days without WEEKEND DAYs</b></td></tr>";
	for ($i = 0; $i < 70; $i++) {
		$end = clone $start;
		$end->modify("+{$i} days");
		$count = Util::count_work_days($start, $end);
		$weight = in_array($end->format('w'), [0, 6]) ? 'bold' : 'normal';
		echo "<tr><td>{$start->format('Y-m-d D')}</td><td style='font-weight: {$weight}'>{$end->format('Y-m-d D')}</td><td>{$count}</td></tr>";
	}
	echo "</table>";
	echo "<p>****************************************************************</p>";
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='3'><b>Counting days without SATURDAYs</b></td></tr>";
	for ($i = 0; $i < 70; $i++) {
		$end = clone $start;
		$end->modify("+{$i} days");
		$count = Util::count_work_days($start, $end, 6);
		$weight = in_array($end->format('w'), [6]) ? 'bold' : 'normal';
		echo "<tr><td>{$start->format('Y-m-d D')}</td><td style='font-weight: {$weight}'>{$end->format('Y-m-d D')}</td><td>{$count}</td></tr>";
	}
	echo "</table>";
	echo "<p>****************************************************************</p>";
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='3'><b>Counting days without SUNDAYs</b></td></tr>";
	for ($i = 0; $i < 70; $i++) {
		$end = clone $start;
		$end->modify("+{$i} days");
		$count = Util::count_work_days($start, $end, 7);
		$weight = in_array($end->format('w'), [0]) ? 'bold' : 'normal';
		echo "<tr><td>{$start->format('Y-m-d D')}</td><td style='font-weight: {$weight}'>{$end->format('Y-m-d D')}</td><td>{$count}</td></tr>";
	}
	echo "</table>";
}

function test_get_end_date() {
	$start = new DateTime('2017-12-01');
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='2'><b>Calculating end date for {$start->format('Y-m-d D')}</b></td></tr>";
	for ($i = 0; $i < 40; $i++) {
		$days = $i + 1;
		$end = clone $start;
		$end->modify("+{$i} days");
		$end = Util::get_end_date($start, $days, null);
		echo "<tr><td>{$days}</td><td>{$end->format('Y-m-d D')}</td></tr>";
	}
	echo "</table>";
}

function test_get_work_end_date() {
	$start = new DateTime('2017-12-01');
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='2'><b>Calculating work end date for {$start->format('Y-m-d D')} minus WEEKEND DAYs</b></td></tr>";
	for ($i = 0; $i < 40; $i++) {
		$days = $i + 1;
		$end = clone $start;
		$end->modify("+{$i} days");
		$end = Util::get_work_end_date($start, $days, [6, 7], null);
		echo "<tr><td>{$days}</td><td>{$end->format('Y-m-d D')}</td></tr>";
	}
	echo "</table>";
	echo "<p>****************************************************************</p>";
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='2'><b>Calculating work end date for {$start->format('Y-m-d D')} minus SATURDAYs</b></td></tr>";
	for ($i = 0; $i < 40; $i++) {
		$days = $i + 1;
		$end = clone $start;
		$end->modify("+{$i} days");
		$end = Util::get_work_end_date($start, $days, [6], null);
		echo "<tr><td>{$days}</td><td>{$end->format('Y-m-d D')}</td></tr>";
	}
	echo "</table>";
	echo "<p>****************************************************************</p>";
	echo "<table cellpadding='10'>";
	echo "<tr><td colspan='2'><b>Calculating work end date for {$start->format('Y-m-d D')} minus SUNDAYs</b></td></tr>";
	for ($i = 0; $i < 40; $i++) {
		$days = $i + 1;
		$end = clone $start;
		$end->modify("+{$i} days");
		$end = Util::get_work_end_date($start, $days, [0], null);
		echo "<tr><td>{$days}</td><td>{$end->format('Y-m-d D')}</td></tr>";
	}
	echo "</table>";
}