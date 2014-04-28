<?php

namespace Micaherne\Bitboards;

/** 
 * A class representing a 64-bit integer for the purposes of bitboards.
 * 
 * @author Michael Aherne
 *
 */
class Int64 {
	private $a = 0;
	private $b = 0;
	
	public function __construct($high = 0, $low = 0) {
		$this->a = $high;
		$this->b = $low;
	}

	public function out() {
		return str_pad(decbin($this->a), 32, '0', STR_PAD_LEFT)
		. str_pad(decbin($this->b), 32, '0', STR_PAD_LEFT);
	}
	
	/**
	 * Binary AND
	 * 
	 * Unfortunately we can't just call this and() as it's a reserved word :(
	 * 
	 * @param Int64 $other
	 * @return \Micaherne\Bitboards\Int64
	 */
	public function bAnd(Int64 $other) {
		return new static($this->a & $other->a, $this->b & $other->b);
	}
	
	public function bOr(Int64 $other) {
		return new static($this->a | $other->a, $this->b | $other->b);
	}
	
	public function bXor(Int64 $other) {
		return new static($this->a ^ $other->a, $this->b ^ $other->b);
	}
	
	public function bNot() {
		return new static(~ $this->a, ~ $this->b);
	}
	
	public function bShiftLeft($count) {
		if ($count >= 32) {
			return new static($this->b << ($count - 32), 0);
		} else {
			$newa = $this->a << $count;
			// Sign bit is copied on right shifting
			$carriedbits = $this->b >> (32 - $count) & (pow(2, $count) - 1);
			return new static($newa | $carriedbits, $this->b << $count);
		}
	}
	
	public function bShiftRight($count) {
		if ($count == 0) {
			return $this;
		}

		if ($count >= 32) {
			$low = $this->a | pow(2, 31);
			$low ^= pow(2, 31);
			return new static(0, $low >> ($count - 32));
		} else {
			// Set sign bit to zero, as it's copied on right shifting
			$high = ($this->a >> $count) & (pow(2, 32 - $count) - 1);
			$carriedbits = $this->a & (pow(2, $count) - 1);
			return new static($high, ($this->b >> $count) | ($carriedbits << (32 - $count)));
		}
	}
		
	public function magicIndex(Int64 $magicNumber, $bitCount) {
		return (($this->b * $magicNumber->b) >> (64 - $bitCount)) & (pow(2, $bitCount) - 1);
	}

	public function setBit($index) {
		if ($index < 32) {
			$this->b |= pow(2, $index);
		} else {
			$this->a |= pow(2, ($index - 32));
		}
		return $this; // for chaining
	}
	
	public function unsetBit($index) {
		if ($index < 32) {
			$this->b |= pow(2, $index);
			$this->b ^= pow(2, $index);
		} else {
			$this->a |= pow(2, ($index - 32));
			$this->a ^= pow(2, ($index - 32));
		}
		return $this; // for chaining
	}
	
	/** 
	 * The value of a bit
	 * 
	 * @param int $index bit index (low = 0, high = 63)
	 * @return int
	 */
	public function getBit($index) {
		if ($index < 32) {
			return ($this->b >> $index) & 1;
		} else {
			return ($this->a >> ($index - 32)) & 1;
		}
	}
	
	/**
	 * Get an array of the indexes of all set bits.
	 * 
	 * @todo improve performance
	 */
	public function getSetBits() {
		$result = array();
		for($i = 0; $i < 64; $i++) {
			if ($this->getBit($i)) {
				$result[] = $i;
			}
		}
		return $result;
	}
	
	public static function randomMagicNumber() {
		$a = mt_rand(0, PHP_INT_MAX) | mt_rand(0, PHP_INT_MAX) | mt_rand(0, PHP_INT_MAX);
		$b = mt_rand(0, PHP_INT_MAX) | mt_rand(0, PHP_INT_MAX) | mt_rand(0, PHP_INT_MAX);
		return new Int64($a, $b);
	}
	
	public function lsb() {
		for ($i = 0; $i < 64; $i++) {
			if ($this->getBit($i) == 1) {
				return $i;
			}
		}
	}
	
	public function hasBitsSet() {
		return !($this->a == 0 && $this->b == 0);
	}
	
	public function __toString() {
		return $this->out();
	}
}