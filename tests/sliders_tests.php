<?php

use Micaherne\Bitboards\Position;
use Micaherne\Bitboards\Sliders;
use Micaherne\Bitboards\Piece;
use Micaherne\Bitboards\UtilBB;

require_once __DIR__.'/../vendor/autoload.php';

class SlidersTests extends PHPUnit_Framework_TestCase {

	public function testOne() {
		$pos = new Position();
		$pos->startPosition();
		$sliders = new Sliders();
		$this->assertCount(64, $sliders->magicMovesBishop);
		$this->assertCount(64, $sliders->magicMovesRook);
		$occupied = $pos->getPieceBitboard(Piece::$WHITE, Piece::$OCCUPIED)->bOr($pos->getPieceBitboard(Piece::$BLACK, Piece::$OCCUPIED));
		$friendly = $pos->getPieceBitboard(Piece::$WHITE, Piece::$OCCUPIED);
		$e4bishopAttacks = $sliders->bishopAttacks(28, $occupied, $friendly);
		$a1rookAttacks = $sliders->rookAttacks(0, $occupied, $friendly);
		//echo $occupied;

		$kingAttacks = UtilBB::$kingAttacks[5];
		echo $kingAttacks;
	}

}