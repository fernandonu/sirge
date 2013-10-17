<?php
	function time_start() {
		global $starttime;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;
	}
	 
	function time_end() {
		global $starttime;
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		return round(($mtime - $starttime),12);
	}
?>