<?php

use Micaherne\Bitboards\Int64;
use Micaherne\Bitboards\BitBoard;
use Micaherne\Bitboards\Move;

require_once __DIR__.'/../vendor/autoload.php';

class MoveTests extends PHPUnit_Framework_TestCase {

    public function testSquareToAlgebraic() {
        $this->assertEquals('c1', Move::squareToAlgebraic(2));
        $this->assertEquals('e4', Move::squareToAlgebraic(28));
    }

    public function testToString() {
        $move = new Move(array(2, 28));
        $this->assertEquals(2, $move->from);
        $this->assertEquals(28, $move->to);
        $this->assertEquals('c1e4', $move->__toString());
    }

}