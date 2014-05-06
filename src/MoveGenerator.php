<?php

namespace Micaherne\Bitboards;

class MoveGenerator {

	private static $sliders;

	public static function init() {
		self::$sliders = new Sliders();
	}

	public static function generateMoves(Position $p, $whiteToMove) {

		$result = array();
		$sideToMove = $whiteToMove ? Piece::$WHITE : Piece::$BLACK;
		$opposingSide = $whiteToMove ? Piece::$BLACK : Piece::$WHITE;

		$friendlyPieces = $p->getPieceBitboard($sideToMove, Piece::$OCCUPIED);
		$enemyPieces = $p->getPieceBitboard($opposingSide, Piece::$OCCUPIED);
		$occupied = $friendlyPieces->bOr($enemyPieces);
		$enemyPiecesAndEmpty = $p->getPieceBitboard($sideToMove, Piece::$OCCUPIED)->bNot();
		$emptySquares = $friendlyPieces->bOr($enemyPieces)->bNot();

		// Knight moves
		$knights = $p->getPieceBitboard($sideToMove, Piece::$KNIGHT);
		while ($knights->hasBitsSet()) {
			$sq = $knights->lsb();
			$moves = UtilBB::$knightAttacks[$sq]->bAnd($enemyPiecesAndEmpty);
			while($moves->hasBitsSet()) {
				$msq = $moves->lsb();
				$result[] = array($sq, $msq);
				$moves->unsetBit($msq);
			}

			$knights->unsetBit($sq);
		}

		// Pawn moves
		$pawns = $p->getPieceBitboard($sideToMove, Piece::$PAWN);
		$moves = self::advancePawns($pawns, $whiteToMove)->bAnd($emptySquares);
		$doubleMoves = self::advancePawns($moves->bAnd(UtilBB::$pawnDoubleMoveMask[$sideToMove]), $sideToMove)->bAnd($emptySquares);

		// If promotions exist, generate them first and remove from moves
		$promotionsExist = $moves->bAnd(UtilBB::$pawnPromotionMask[$sideToMove])->hasBitsSet();
		if ($promotionsExist) {
			$promotionMoves = $moves->bAnd(UtilBB::$pawnPromotionMask[$sideToMove]);
			while($promotionMoves->hasBitsSet()) {
				$msq = $promotionMoves->lsb();
				foreach(Piece::$promotionPieces as $piece) {
					$result[] = array($msq - UtilBB::$pawnMoveOffset[$sideToMove][0], $msq, $piece * UtilBB::$colourMultiplier[$sideToMove]);
				}
				$promotionMoves->unsetBit($msq);
				$moves->unsetBit($msq);
			}
		}

		while($moves->hasBitsSet()) {
			$msq = $moves->lsb();
			$result[] = array($msq - UtilBB::$pawnMoveOffset[$sideToMove][0], $msq);
			$moves->unsetBit($msq);
		}
		while($doubleMoves->hasBitsSet()) {
			$msq = $doubleMoves->lsb();
			$result[] = array($msq - UtilBB::$pawnMoveOffset[$sideToMove][1], $msq);
			$doubleMoves->unsetBit($msq);
		}

		// Pawn captures
		$pawns = $p->getPieceBitboard($sideToMove, Piece::$PAWN);
		while($pawns->hasBitsSet()) {
			$bsq = $pawns->lsb();
			$moves = UtilBB::$pawnAttacks[$sideToMove][$sq]->bAnd($enemyPieces->bOr($p->ep));
			while($moves->hasBitsSet()) {
				$msq = $moves->lsb();
				$result[] = array($bsq, $msq);
				$moves->unsetBit($msq);
			}
			$pawns->unsetBit($bsq);
		}

		// Bishop moves
		$bishops = $p->getPieceBitboard($sideToMove, Piece::$BISHOP);
		while($bishops->hasBitsSet()) {
			$bsq = $bishops->lsb();
			$moves = self::$sliders->bishopAttacks($bsq, $occupied, $friendlyPieces);
			while($moves->hasBitsSet()) {
				$msq = $moves->lsb();
				$result[] = array($bsq, $msq);
				$moves->unsetBit($msq);
			}
			$bishops->unsetBit($bsq);
		}

		// Rook moves
		$rooks = $p->getPieceBitboard($sideToMove, Piece::$ROOK);
		while($rooks->hasBitsSet()) {
			$bsq = $rooks->lsb();
			$moves = self::$sliders->rookAttacks($bsq, $occupied, $friendlyPieces);
			while($moves->hasBitsSet()) {
				$msq = $moves->lsb();
				$result[] = array($bsq, $msq);
				$moves->unsetBit($msq);
			}
			$rooks->unsetBit($bsq);
		}

		// Queen moves
		$queens = $p->getPieceBitboard($sideToMove, Piece::$QUEEN);
		while($queens->hasBitsSet()) {
			$bsq = $queens->lsb();
			$moves = self::$sliders->bishopAttacks($bsq, $occupied, $friendlyPieces)
				->bOr(self::$sliders->rookAttacks($bsq, $occupied, $friendlyPieces));
			while($moves->hasBitsSet()) {
				$msq = $moves->lsb();
				$result[] = array($bsq, $msq);
				$moves->unsetBit($msq);
			}
			$queens->unsetBit($bsq);
		}

		// King moves
		$kings = $p->getPieceBitboard($sideToMove, Piece::$KING);
		while($kings->hasBitsSet()) {
			$bsq = $kings->lsb();
			$moves = UtilBB::$kingAttacks[$sq]->bAnd($enemyPiecesAndEmpty);
			while($moves->hasBitsSet()) {
				$msq = $moves->lsb();
				$result[] = array($bsq, $msq);
				$moves->unsetBit($msq);
			}
			$kings->unsetBit($bsq);
		}

		// Castling
		$castling = $p->castling[$sideToMove];
		if ($castling > 0) {
			for($i = 0; $i <= 1; $i++) {
				if ($castling && pow(2, $i) != 0) {
					if (!$occupied->bAnd(UtilBB::$castlingUnoccupied[$sideToMove][$i])->hasBitsSet()) {
						// $bsq is still set from above
						$result[] = array($bsq, $bsq + (($i << 2) - 2));
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Return a bitboard representing the given pawns moved forward one rank.
	 *
	 * @param BitBoard $pawns the position of the side to move's pawns
	 * @param boolean $whiteToMove
	 */
	public static function advancePawns(BitBoard $pawns, $whiteToMove) {
		if ($whiteToMove) {
			return $pawns->bShiftLeft(8);
		} else {
			return $pawns->bShiftRight(8);
		}
	}

}

MoveGenerator::init();