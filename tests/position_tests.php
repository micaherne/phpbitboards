<?php

use Micaherne\Bitboards\Int64;
use Micaherne\Bitboards\Position;
use Micaherne\Bitboards\Piece;
use Micaherne\Bitboards\BitBoard;

require_once __DIR__.'/../vendor/autoload.php';

class PositionTests extends PHPUnit_Framework_TestCase {
	
	public function testInit() {
		$p = new Position();
		$p->startPosition();
		$this->assertEquals(Piece::$KNIGHT, $p->getBoardPiece(1));
		$bb = $p->getPieceBitboard(Piece::$WHITE, Piece::$BISHOP);
		$this->assertEquals(new BitBoard(0, 36), $bb);
	}
	
	public function testMoveGen() {
		$p = new Position();
		$p->startPosition();
		$moves = $p->moveGen();
		$this->assertCount(20, $moves);
	}
	
}