<?php

namespace Micaherne\Bitboards;

class UtilBB {

	private static $initialised = false;

	public static $plus1dir;
	public static $plus7dir;
	public static $plus8dir;
	public static $plus9dir;
	public static $minus1dir;
	public static $minus7dir;
	public static $minus8dir;
	public static $minus9dir;

	public static $rankMask;
	public static $fileMask;

	public static $pawnAttacks; // [2][65] - one per colour
	public static $knightAttacks;
	public static $rookAttacks;
	public static $bishopAttacks;
	public static $kingAttacks;

	// Squares between king and rook: array[colour][0 = K, 1 = Q]
	public static $castlingUnoccupied;

	static $knightsq = array(-17, -15, -10, -6, 6, 10, 15, 17);
	static $bishopsq = array(-9, -7, 7, 9);
	static $rooksq = array(-8, -1, 1, 8);

	public static $intervening;
	public static $directions;

	public static $pawnMoveOffset = array(array()); //[2][2] - colour, squaresmoved
	public static $pawnDoubleMoveMask = array();
	public static $pawnPromotionMask = array();

	public static $colourMultiplier = array();

	public static function init() {
		if (self::$initialised) {
			return;
		}

		self::$plus1dir = array();
		self::$plus7dir = array();
		self::$plus8dir = array();
		self::$plus9dir = array();
		self::$minus1dir = array();
		self::$minus7dir = array();
		self::$minus8dir = array();
		self::$minus9dir = array();

		self::$pawnAttacks = array(array(), array());
		self::$knightAttacks = array();
		self::$rookAttacks = array();
		self::$bishopAttacks = array();
		self::$kingAttacks = array();

		self::$intervening = array(array());
		self::$directions = array(array());

		for ($i = 0; $i < 64; $i++) {
			self::$plus1dir[$i] = new BitBoard();
			self::$plus7dir[$i] = new BitBoard();
			self::$plus8dir[$i] = new BitBoard();
			self::$plus9dir[$i] = new BitBoard();
			self::$minus1dir[$i] = new BitBoard();
			self::$minus7dir[$i] = new BitBoard();
			self::$minus8dir[$i] = new BitBoard();
			self::$minus9dir[$i] = new BitBoard();
		}

		/*
		 masks to select bits on a specific rank or file
		*/
		self::$rankMask[0] = new BitBoard(0, 255);
		for ($i = 1; $i < 8; $i++) {
			self::$rankMask[$i] = self::$rankMask[$i - 1]->bShiftLeft(8);
		}
		self::$fileMask[0] = new BitBoard(0, 1);
		for ($i = 1; $i < 8; $i++) {
			self::$fileMask[0] = self::$fileMask[0]->bOr(self::$fileMask[0]->bShiftLeft(8));
		}
		for ($i = 1; $i < 8; $i++) {
			self::$fileMask[$i] = self::$fileMask[$i - 1]->bShiftLeft(1);
		}

		for ($i = 0; $i < 64; $i++) {
			self::$pawnAttacks[Piece::$WHITE][$i] = new BitBoard();
			if ($i < 56) {
				for ($j = 2; $j < 4; $j++) {
					$sq = $i + self::$bishopsq[$j];
					if ((abs(self::rank($sq) - self::rank($i)) == 1) && (abs(self::file($sq) - self::file($i)) == 1)
							&& ($sq < 64) && ($sq > -1)) {
								self::$pawnAttacks[Piece::$WHITE][$i]->setBit($sq);
							}
				}
			}
			self::$pawnAttacks[Piece::$BLACK][$i] = new BitBoard();
			if ($i > 7)
				for ($j = 0; $j < 2; $j++) {
					$sq = $i + self::$bishopsq[$j];
					if ((abs(self::rank($sq) - self::rank($i)) == 1) && (abs(self::file($sq) - self::file($i)) == 1)
							&& ($sq < 64) && ($sq > -1)) {
								self::$pawnAttacks[Piece::$BLACK][$i]->setBit($sq);
							}
				}
		}

		// Pawn constants
		self::$pawnMoveOffset = array(Piece::$BLACK => array(-8, -16), Piece::$WHITE => array(8, 16));
		self::$pawnDoubleMoveMask = array(Piece::$BLACK => self::$rankMask[6], Piece::$WHITE => self::$rankMask[2]);
		self::$pawnPromotionMask = array(Piece::$BLACK => self::$rankMask[0], Piece::$WHITE =>self::$rankMask[7]);

		self::$colourMultiplier = array(Piece::$BLACK => -1, Piece::$WHITE => 1);

		/*
		 initialize knight attack board
		*/
		for ($i = 0; $i < 64; $i++) {
			self::$knightAttacks[$i] = new BitBoard();
			$frank = self::rank($i);
			$ffile = self::file($i);
			for ($j = 0; $j < 8; $j++) {
				$sq = $i + self::$knightsq[$j];
				if (($sq < 0) || ($sq > 63)) {
					continue;
				}
				$trank = self::rank($sq);
				$tfile = self::file($sq);
				if ((abs($frank - $trank) > 2) || (abs($ffile - $tfile) > 2)) {
					continue;
				}
				self::$knightAttacks[$i]->setBit($sq);
			}
		}

		/*
		 initialize bishop/queen attack boards and masks
		*/
		for ($i = 0; $i < 64; $i++) {
			for ($j = 0; $j < 4; $j++) {
				$sq = $i;
				$lastsq = $sq;
				$sq = $sq + self::$bishopsq[$j];
				while ((abs(self::rank($sq) - self::rank($lastsq)) == 1) &&
						(abs(self::file($sq) - self::file($lastsq)) == 1) && ($sq < 64) && ($sq > -1)) {
							if (self::$bishopsq[$j] == 7) {
								self::$plus7dir[$i]->setBit($sq);
							} else if (self::$bishopsq[$j] == 9) {
								self::$plus9dir[$i]->setBit($sq);
							} else if (self::$bishopsq[$j] == -7) {
								self::$minus7dir[$i]->setBit($sq);
							} else {
								self::$minus9dir[$i]->setBit($sq);
							}
							$lastsq = $sq;
							$sq = $sq + self::$bishopsq[$j];
						}
			}
		}

		self::$plus1dir[64] = 0;
		self::$plus7dir[64] = 0;
		self::$plus8dir[64] = 0;
		self::$plus9dir[64] = 0;
		self::$minus1dir[64] = 0;
		self::$minus7dir[64] = 0;
		self::$minus8dir[64] = 0;
		self::$minus9dir[64] = 0;
		/*
		 initialize rook/queen attack boards
		*/
		for ($i = 0; $i < 64; $i++) {
			for ($j = 0; $j < 4; $j++) {
				$sq = $i;
				$lastsq = $sq;
				$sq = $sq + self::$rooksq[$j];
				while ((((abs(self::rank($sq) - self::rank($lastsq)) == 1) &&
						(abs(self::file($sq) - self::file($lastsq)) == 0)) ||
						((abs(self::rank($sq) - self::rank($lastsq)) == 0) &&
								(abs(self::file($sq) - self::file($lastsq)) == 1))) && ($sq < 64) &&
						($sq > -1)) {
							if (self::$rooksq[$j] == 1) {
								self::$plus1dir[$i]->setBit($sq);
							} else if (self::$rooksq[$j] == 8) {
								self::$plus8dir[$i]->setBit($sq);
							} else if (self::$rooksq[$j] == -1) {
								self::$minus1dir[$i]->setBit($sq);
							} else {
								self::$minus8dir[$i]->setBit($sq);
							}
							$lastsq = $sq;
							$sq = $sq + self::$rooksq[$j];
						}
			}
		}
		/*
		 initialize bishop attack board
		*/
		for ($i = 0; $i < 64; $i++) {
			self::$bishopAttacks[$i] =
				self::$plus9dir[$i]->bOr(self::$minus9dir[$i])->bOr(self::$plus7dir[$i])->bOr(self::$minus7dir[$i]);
		}
		/*
		 initialize rook attack board
		*/
		for ($i = 0; $i < 64; $i++) {
			self::$rookAttacks[$i] = self::$fileMask[self::file($i)]->bOr(self::$rankMask[self::rank($i)]);
		}
		/*
		 initialize king attack board
		*/
		for ($i = 0; $i < 64; $i++) {
			self::$kingAttacks[$i] = new BitBoard();
			for ($j = 0; $j < 64; $j++) {
				if (self::distance($i, $j) == 1)
					self::$kingAttacks[$i]->setBit($j);
			}
		}

		/*
		 direction[sq1][sq2] gives the "move direction" to move from
		sq1 to sq2.  intervening[sq1][sq2] gives a bit vector that indicates
		which squares must be unoccupied in order for <sq1> to attack <sq2>,
		assuming a sliding piece is involved.  to use this, you simply have
		to Or(intervening[sq1][sq2],occupied_squares) and if the result is
		"0" then a sliding piece on sq1 would attack sq2 and vice-versa.
		*/
		for ($i = 0; $i < 64; $i++) {
			for ($j = 0; $j < 64; $j++) {
					self::$intervening[$i][$j] = new BitBoard();
					self::$directions[$i][$j] = 0;
			}
			$sqs = clone(self::$plus1dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = 1;
				self::$intervening[$i][$j] = self::$plus1dir[$i]->bXor(self::$plus1dir[$j - 1]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$plus7dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = 7;
				self::$intervening[$i][$j] = self::$plus7dir[$i]->bXor(self::$plus7dir[$j - 7]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$plus8dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = 8;
				self::$intervening[$i][$j] = self::$plus8dir[$i]->bXor(self::$plus8dir[$j - 8]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$plus9dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = 9;
				self::$intervening[$i][$j] = self::$plus9dir[$i]->bXor(self::$plus9dir[$j - 9]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$minus1dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = -1;
				self::$intervening[$i][$j] = self::$minus1dir[$i]->bXor(self::$minus1dir[$j + 1]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$minus7dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = -7;
				self::$intervening[$i][$j] = self::$minus7dir[$i]->bXor(self::$minus7dir[$j + 7]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$minus8dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = -8;
				self::$intervening[$i][$j] = self::$minus8dir[$i]->bXor(self::$minus8dir[$j + 8]);
				$sqs->unsetBit($j);
			}
			$sqs = clone(self::$minus9dir[$i]);
			while ($sqs->hasBitsSet()) {
				$j = $sqs->lsb();
				self::$directions[$i][$j] = -9;
				self::$intervening[$i][$j] = self::$minus9dir[$i]->bXor(self::$minus9dir[$j + 9]);
				$sqs->unsetBit($j);
			}
		}

		// Initialise castling intervening squares
		self::$castlingUnoccupied = array(array(), array());
		$kcastle = new BitBoard(0, 0x60);
		$qcastle = new BitBoard(0, 14);
		self::$castlingUnoccupied[Piece::$WHITE][0] = $kcastle;
		self::$castlingUnoccupied[Piece::$BLACK][0] = $kcastle->bShiftLeft(56);
		self::$castlingUnoccupied[Piece::$WHITE][1] = $qcastle;
		self::$castlingUnoccupied[Piece::$BLACK][1] = $qcastle->bShiftLeft(56);


		self::$initialised = true;
	}

	public static function rank($sq) {
		return $sq >> 3;
	}

	public static function file($sq) {
		return $sq & 7;
	}

	public static function fileDistance($a, $b) {
		return abs(self::file($a) - self::file($b));
	}

	public static function rankDistance($a, $b) {
		return abs(self::rank($a) - self::rank($b));
	}

	public static function distance($a, $b) {
		return max(array(self::fileDistance($a, $b), self::rankDistance($a, $b)));
	}

}

UtilBB::init();