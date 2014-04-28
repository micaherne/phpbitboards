<?php

use Micaherne\Bitboards\UtilBB;
use Micaherne\Bitboards\Piece;
class init_tests extends PHPUnit_Framework_TestCase {
	
	public function setUp() {
		UtilBB::init();
	}
	
	public function testInitDirections() {
		// echo UtilBB::$plus9dir[0]->out();
		// echo UtilBB::$intervening[0][63]->out();
		$this->assertInstanceOf('Micaherne\Bitboards\BitBoard', UtilBB::$intervening[0][63]);
	}
	
}