<?php

namespace Micaherne\Bitboards;

class MagicBitBoards {
	
	public static $initialised = false;
	
	public static $occupancyMaskRook;
	public static $occupancyMaskBishop;
	
	public static $occupancyVariation;
	public static $occupancyAttackSet;
	
	public static $magicNumberRook;
	public static $magicNumberShiftsRook;
	public static $magicNumberBishop;
	public static $magicNumberShiftsBishop;
	
	public static $magicMovesRook;
	public static $magicMovesBishop;
	
	public static function init() {
		if (self::$initialised) {
			return;
		}
		UtilBB::init();

		self::$occupancyMaskRook = array();
		self::$occupancyMaskBishop = array();
		self::$occupancyVariation = array(array());
		self::$occupancyAttackSet = array(array());
		self::$magicNumberRook = array();
		self::$magicNumberShiftsRook = array();
		self::$magicNumberBishop = array();
		self::$magicNumberShiftsBishop = array();
		self::$magicMovesRook = array(array());
		self::$magicMovesBishop = array(array());
		
		
		self::generateOccupancyMasks();
		self::generateOccupancyVariations(false);
		self::generateOccupancyVariations(true);
		self::generateMagicNumbers(false);
		self::generateMagicNumbers(true);
		self::generateMoveDatabase(false);
		self::generateMoveDatabase(true);
		self::$initialised = true;
	}
	
	private static function generateOccupancyMasks() {
		$outerSquares = UtilBB::$fileMask[0]->bOr(UtilBB::$fileMask[7])
			->bOr(UtilBB::$rankMask[0])->bOr(UtilBB::$rankMask[7])->bNot();
		for($bit = 0; $bit < 64; $bit++) {
			self::$occupancyMaskRook[$bit] = $outerSquares->bAnd(UtilBB::$rookAttacks[$bit])->unsetBit($bit); 
			self::$occupancyMaskBishop[$bit] = $outerSquares->bAnd(UtilBB::$bishopAttacks[$bit])->unsetBit($bit); 
		}
	}
	
	private static function generateOccupancyVariations($isRook) {
		for ($bit = 0; $bit < 64; $bit++) {
			$mask = $isRook ? self::$occupancyMaskRook[$bit] : self::$occupancyMaskBishop[$bit];
			$setBitsInMask = $mask->getSetBits();
			$bitCount[$bit] = count($setBitsInMask);
			$variationCount = 1 << $bitCount[$bit];
			for ($i = 0; $i < $variationCount; $i++) {
				self::$occupancyVariation[$bit][$i] = new BitBoard();
				self::$occupancyAttackSet[$bit][$i] = new BitBoard();
				$tempi = new BitBoard(0, $i); // just to count bits :(
				$setBitsInIndex = $tempi->getSetBits();
				unset($tempi);
				for ($j = 0; $j < count($setBitsInIndex); $j++) {
					self::$occupancyVariation[$bit][$i]->setBit($setBitsInMask[$setBitsInIndex[$j]]);
				}
				
				if ($isRook) {
					for ($j = $bit + 8; $j < 56 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j += 8) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 8; $j >= 8 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j -= 8) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit + 1; $j % 8 != 7 && $j % 8 != 0 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j++) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 1; $j % 8 != 7 && $j % 8 != 0 && $j > 0 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j--) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
				} else {
					for ($j = $bit + 9; $j % 8 != 7 && $j % 8 != 0 && $j <= 55 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j += 9) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 9; $j % 8 != 7 && $j % 8 != 0 && $j >= 8 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j -= 9) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit + 7; $j % 8 != 7 && $j % 8 != 0 && $j <= 55 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j += 7) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 7; $j % 8 != 7 && $j % 8 != 0 && $j >= 8 && (self::$occupancyVariation[$bit][$i]->getBit($j) == 0); $j -= 7) {
						if ($j >= 0 && $j < 64) {
							self::$occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
				}
			}
		}
	}
	
	private static function generateMagicNumbers($isRook) {
				
		for ($bit = 0; $bit < 64; $bit++) {
			$occupancyMask = $isRook ? self::$occupancyMaskRook[$bit] : self::$occupancyMaskBishop[$bit];
			$bitCount = count($occupancyMask->getSetBits());
			$variationCount = 1 << $bitCount;
			$usedBy = array();
			$attempts = 0;
			do {
				$magicNumber = BitBoard::randomMagicNumber();
				for ($j = 0; $j < $variationCount; $j++) {
					$usedBy[$j] = 0;
					$attempts++;
					
					$fail = false;
					for ($i = 0; $i < $variationCount && !$fail; $i++) {
						$index = (self::$occupancyVariation[$bit][$i]->magicIndex($magicNumber, $bitCount));
					}
					$fail = isset($usedBy[$index]) && $usedBy[$index] != 0 && $usedBy[$index] != self::$occupancyAttackSet[$bit][$i];
					$usedBy[$index] = 0; //self::$occupancyAttackSet[$bit][$i]; // ????
				}
			} while ($fail);
			
			if ($isRook) {
				self::$magicNumberRook[$bit] = $magicNumber;
				self::$magicNumberShiftsRook[$bit] = (64 - $bitCount);
			} else {
				self::$magicNumberBishop[$bit] = $magicNumber;
				self::$magicNumberShiftsBishop[$bit] = (64 - $bitCount);
			}
		}
		
		
	}
	
	private static function generateMoveDatabase($isRook) {
		for ($bit = 0; $bit < 64; $bit++) {
			$bits = $isRook ? self::$occupancyMaskRook[$bit]->getSetBits() : self::$occupancyMaskBishop[$bit]->getSetBits();
			$bitCount = count($bits);
			$variations = 1 << $bitCount;
			
			for ($i = 0; $i < $variations; $i++) {
				$validMoves = new BitBoard();
				if ($isRook) {
					$magicIndex = self::$occupancyVariation[$bit][$i]->magicIndex(self::$magicNumberRook[$bit], $bitCount);
					for ($j = $bit + 8; $j <= 63; $j += 8) { 
						$validMoves->setBit($j); 
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break; 
						}
					}
					for ($j = $bit - 8; $j >= 0; $j -= 8) { 
						$validMoves->setBit($j);
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break; 
						}
					}
					for ($j = $bit + 1; $j % 8 != 0; $j++){ 
						$validMoves->setBit($j);
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break; 
						}
					}
					for ($j = $bit - 1; $j % 8 != 7 && $j >= 0; $j--) {
						$validMoves->setBit($j) ;
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break; 
						}
					}
					
					self::$magicMovesRook[$bit][$magicIndex] = $validMoves;
				} else {
					$magicIndex = self::$occupancyVariation[$bit][$i]->magicIndex(self::$magicNumberBishop[$bit], $bitCount);
					for ($j = $bit + 9; $j % 8 != 0 && $j <= 63; $j += 9) { 
						$validMoves->setBit($j) ;
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break; 
						}
					}
					for ($j = $bit - 9; $j % 8 != 7 && $j >= 0; $j -= 9) { 
						$validMoves->setBit($j) ;
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}	
					}
					for ($j = $bit + 7; $j % 8 != 7 && $j <= 63; $j += 7) {
						$validMoves->setBit($j) ;
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}	
					}
					for ($j = $bit - 7; $j % 8 != 0 && $j >= 0; $j -= 7) {
						$validMoves->setBit($j) ;
						if ((self::$occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					
					self::$magicMovesBishop[$bit][$magicIndex] = $validMoves;
				}
			}
		}
	}
	
}

MagicBitBoards::init();