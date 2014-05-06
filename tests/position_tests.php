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

	public function testFromFen() {
		$p = new Position();
		$p->fromFEN('rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2');
		$this->assertEquals(3, $p->castling[Piece::$WHITE]);
		$this->assertEquals(1, $p->ep->getBit(42));
		$this->assertCount(1, $p->ep->getSetBits());
		$this->assertEquals(0, $p->halfmove);
		$this->assertEquals(2, $p->fullmove);
	}

}