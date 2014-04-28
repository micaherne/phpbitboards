<?php

use Micaherne\Bitboards\Position;
use Micaherne\Bitboards\MoveGenerator;
use Micaherne\Bitboards\UtilBB;

class movegen_tests extends PHPUnit_Framework_TestCase {
	
	public function testInitPositionMoves() {
		$p = new Position();
		$p->startPosition();
		$moves = MoveGenerator::generateMoves($p, true);
		// print_r($moves);
		$this->assertCount(20, $moves);
	}
	
}