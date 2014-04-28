<?php

use Micaherne\Bitboards\Int64;
use Micaherne\Bitboards\BitBoard;
require_once __DIR__.'/../vendor/autoload.php';

class Int64Tests extends PHPUnit_Framework_TestCase {
	
	public function testOne() {
		$i1 = new Int64();
		$this->assertEquals(64, strlen($i1->out()));
		
		$i2 = new Int64(0, 16);
		$this->assertEquals($i1, $i1->bAnd($i2));
		$this->assertEquals($i2, $i1->bOr($i2));
		$this->assertEquals($i2, $i1->bXor($i2));
		
		$this->assertEquals(1, $i2->getBit(4));
		
		$i3 = new Int64(0, 15);
		$i4 = new Int64(-1, 256 + 12);
		$this->assertEquals(new Int64(0, 12), $i3->bAnd($i4));
		
		$bbp1 = new \Micaherne\Bitboards\BitBoardPieces();
	}
	
	public function testShiftLeft() {
		$i1 = new Int64(0, -1);
		$this->assertEquals(64, strlen($i1));
		$this->assertEquals('0000000000000000000000000000000011111111111111111111111111111111', $i1->__toString());
		$r1 = $i1->bShiftLeft(1);
		$this->assertEquals(64, strlen($r1));
		$this->assertEquals('0000000000000000000000000000000111111111111111111111111111111110', $r1->__toString());
		
		$i2 = new Int64(0, 255);
		$i2 = $i2->bShiftLeft(8)->bShiftLeft(8)->bShiftLeft(8);
		$this->assertEquals('0000000000000000000000000000000011111111000000000000000000000000', $i2->__toString());
		$i2 = $i2->bShiftLeft(8);
		$this->assertEquals('0000000000000000000000001111111100000000000000000000000000000000', $i2->__toString());
	}
	
	public function testShiftRight() {
		$i1 = new Int64(-1, 0);
		$this->assertEquals(64, strlen($i1));
		$this->assertEquals('1111111111111111111111111111111100000000000000000000000000000000', $i1->__toString());
		$r1 = $i1->bShiftRight(1);
		$this->assertEquals(64, strlen($r1));
		$this->assertEquals('0111111111111111111111111111111110000000000000000000000000000000', $r1->__toString());
	
		$i2 = new Int64(255, 0);
		$this->assertEquals('0000000000000000000000001111111100000000000000000000000000000000', $i2->__toString());
		$i3 = $i2->bShiftRight(8);
		$this->assertEquals('0000000000000000000000000000000011111111000000000000000000000000', $i3->__toString());
		$i4 = $i2->bShiftRight(33);
		$this->assertEquals('0000000000000000000000000000000000000000000000000000000001111111', $i4->__toString());
		$i5 = $i2->bShiftRight(39);
		$this->assertEquals('0000000000000000000000000000000000000000000000000000000000000001', $i5->__toString());
	}
	
}