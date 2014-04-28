<?php

namespace Micaherne\Bitboards;

class BitBoard extends Int64 {
	
	public function out() {
		$outrev = strrev(parent::out());
		$result = '';
		for ($i = 0; $i < 8; $i++) {
			$result = substr($outrev, $i * 8, 8) . "\n" . $result;
		}
		return $result;
	}
	
}