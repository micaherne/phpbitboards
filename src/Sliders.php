<?php

namespace Micaherne\Bitboards;

class Sliders {

	public $occupancyMaskRook;
	public $occupancyMaskBishop;
	public $occupancyVariation;
	public $occupancyAttackSet;
	public $magicMovesBishop;
	public $magicMovesRook;

	public function __construct() {
		$this->generateOccupancyMasks();
		$this->generateOccupancyVariations(true);
		$this->generateMoveDatabase(true);
		$this->generateOccupancyVariations(false);
		$this->generateMoveDatabase(false);
	}

	private function generateOccupancyMasksOld() {
		$this->occupancyMaskBishop = array();
		$this->occupancyMaskRook = array();

		$outerSquares = UtilBB::$fileMask[0]->bOr(UtilBB::$fileMask[7])
		->bOr(UtilBB::$rankMask[0])->bOr(UtilBB::$rankMask[7])->bNot();
		for($bit = 0; $bit < 64; $bit++) {
			$this->occupancyMaskRook[$bit] = $outerSquares->bAnd(UtilBB::$rookAttacks[$bit])->unsetBit($bit);
			$this->occupancyMaskBishop[$bit] = $outerSquares->bAnd(UtilBB::$bishopAttacks[$bit])->unsetBit($bit);
		}
	}

	private function generateOccupancyMasks() {
		for ($bit = 0; $bit < 64; $bit++) {
			$mask = new BitBoard();
			for ($i = $bit + 8; $i <= 55; $i += 8) {
				$mask->setBit($i);
			}
			for ($i = $bit - 8; $i >= 8; $i -= 8) {
				$mask->setBit($i);
			}
			for ($i = $bit + 1; $i % 8 != 7 && $i %8 != 0 ; $i++) {
				$mask->setBit($i);
			}
			for ($i = $bit - 1; $i %8 != 7 && $i % 8 != 0 && $i >= 0; $i--) {
				$mask->setBit($i);
			}
			$this->occupancyMaskRook[$bit] = $mask;

			$mask = new BitBoard();
			for ($i = $bit + 9; $i %8 != 7 && $i %8 != 0 && $i <= 55; $i += 9) {
				$mask->setBit($i);
			}
			for ($i = $bit - 9; $i % 8 !=7 && $i % 8 != 0 && $i >= 8; $i -= 9) {
				$mask->setBit($i);
			}
			for ($i = $bit + 7; $i % 8 !=7 && $i % 8 != 0 && $i <= 55; $i += 7) {
				$mask->setBit($i);
			}
			for ($i = $bit - 7; $i % 8 != 7 && $i % 8 != 0 && $i >= 8; $i -= 7) {
				$mask->setBit($i);
			}
			$this->occupancyMaskBishop[$bit] = $mask;
		}
	}

	private function generateOccupancyVariations($isRook) {
		$this->occupancyVariation = array();
		$this->occupancyAttackSet = array();

		for ($bit = 0; $bit < 64; $bit++) {
			$this->occupancyVariation[$bit] = array();
			$this->occupancyAttackSet[$bit] = array();

			$mask = $isRook ? $this->occupancyMaskRook[$bit] : $this->occupancyMaskBishop[$bit];
			$setBitsInMask = $mask->getSetBits();
			$bitCount[$bit] = count($setBitsInMask);
			$variationCount = 1 << $bitCount[$bit];
			for ($i = 0; $i < $variationCount; $i++) {
				$this->occupancyVariation[$bit][$i] = new BitBoard();
				$this->occupancyAttackSet[$bit][$i] = new BitBoard();
				$tempi = new BitBoard(0, $i); // just to count bits :(
				$setBitsInIndex = $tempi->getSetBits();
				unset($tempi);
				for ($j = 0; $j < count($setBitsInIndex); $j++) {
					$this->occupancyVariation[$bit][$i]->setBit($setBitsInMask[$setBitsInIndex[$j]]);
				}

				if ($isRook) {
					for ($j = $bit + 8; $j < 56 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j += 8) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 8; $j >= 8 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j -= 8) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit + 1; $j % 8 != 7 && $j % 8 != 0 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j++) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 1; $j % 8 != 7 && $j % 8 != 0 && $j > 0 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j--) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
				} else {
					for ($j = $bit + 9; $j % 8 != 7 && $j % 8 != 0 && $j <= 55 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j += 9) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 9; $j % 8 != 7 && $j % 8 != 0 && $j >= 8 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j -= 9) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit + 7; $j % 8 != 7 && $j % 8 != 0 && $j <= 55 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j += 7) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
					for ($j = $bit - 7; $j % 8 != 7 && $j % 8 != 0 && $j >= 8 && ($this->occupancyVariation[$bit][$i]->getBit($j) == 0); $j -= 7) {
						if ($j >= 0 && $j < 64) {
							$this->occupancyAttackSet[$bit][$i]->setBit($j);
						}
					}
				}
			}
		}
	}

	private function generateMoveDatabase($isRook) {
		if ($isRook) {
			$this->magicMovesRook = array();
		} else {
			$this->magicMovesBishop = array();
		}

		for ($bit = 0; $bit < 64; $bit++) {
			$bits = $isRook ? $this->occupancyMaskRook[$bit]->getSetBits() : $this->occupancyMaskBishop[$bit]->getSetBits();
			$bitCount = count($bits);
			$variations = 1 << $bitCount;
			if($isRook) {
				$this->magicMovesRook[$bit] = array();
			} else {
				$this->magicMovesBishop[$bit] = array();
			}
			for ($i = 0; $i < $variations; $i++) {
				$validMoves = new BitBoard();
				if ($isRook) {
					$index = $this->occupancyVariation[$bit][$i]->toHex();
					for ($j = $bit + 8; $j <= 63; $j += 8) {
						$validMoves->setBit($j);
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					for ($j = $bit - 8; $j >= 0; $j -= 8) {
						$validMoves->setBit($j);
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					for ($j = $bit + 1; $j % 8 != 0; $j++){
						$validMoves->setBit($j);
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					for ($j = $bit - 1; $j % 8 != 7 && $j >= 0; $j--) {
						$validMoves->setBit($j) ;
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}

					$this->magicMovesRook[$bit][$index] = $validMoves;
				} else {
					$index = $this->occupancyVariation[$bit][$i]->toHex();
					for ($j = $bit + 9; $j % 8 != 0 && $j <= 63; $j += 9) {
						$validMoves->setBit($j) ;
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					for ($j = $bit - 9; $j % 8 != 7 && $j >= 0; $j -= 9) {
						$validMoves->setBit($j) ;
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					for ($j = $bit + 7; $j % 8 != 7 && $j <= 63; $j += 7) {
						$validMoves->setBit($j) ;
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}
					for ($j = $bit - 7; $j % 8 != 0 && $j >= 0; $j -= 7) {
						$validMoves->setBit($j) ;
						if (($this->occupancyVariation[$bit][$i]->getBit($j)) != 0) {
							break;
						}
					}

					$this->magicMovesBishop[$bit][$index] = $validMoves;
				}
			}
		}
	}

	public function bishopAttacks($square, BitBoard $occupied, BitBoard $friendlyPieces) {
		$occupancyMask = $this->occupancyMaskBishop[$square];
		$occupancyVariation = $occupancyMask->bAnd($occupied);
		$moves = $this->magicMovesBishop[$square][$occupancyVariation->toHex()];
		return $moves->bAnd($friendlyPieces->bNot());
	}

	public function rookAttacks($square, BitBoard $occupied, BitBoard $friendlyPieces) {
		$occupancyMask = $this->occupancyMaskRook[$square];
		$occupancyVariation = $occupancyMask->bAnd($occupied);
		$moves = $this->magicMovesRook[$square][$occupancyVariation->toHex()];
		return $moves->bAnd($friendlyPieces->bNot());
	}

}