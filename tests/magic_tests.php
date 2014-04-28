<?php

use Micaherne\Bitboards\UtilBB;
use Micaherne\Bitboards\MagicBitBoards;

class magic_tests extends PHPUnit_Framework_TestCase {
	
	public function testOne() {
		MagicBitBoards::init();
		$out = fopen("out.txt", "w");
		fputs($out, serialize(MagicBitBoards::$magicMovesBishop));
		fclose($out);
	}
	
}