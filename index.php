<?php

class Int64 {
	private $a = 0;
	private $b = 0;
	
	public function out() {
		return str_pad(decbin($this->a), 32, '0', STR_PAD_LEFT)
			. str_pad(decbin($this->b), 32, '0', STR_PAD_LEFT);
	}
}

class BitBoard {
	
}

$b = new Int64();
echo $b->out();