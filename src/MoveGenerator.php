<?php

namespace Micaherne\Bitboards;

class MoveGenerator {
	
	public static function generateMoves(Position $p, $whiteToMove) {
		$result = array();
		$sideToMove = $whiteToMove ? Piece::$WHITE : Piece::$BLACK;
		$opposingSide = $whiteToMove ? Piece::$BLACK : Piece::$WHITE;
		
		$friendlyPieces = $p->getPieceBitboard($sideToMove, Piece::$OCCUPIED);
		$enemyPieces = $p->getPieceBitboard($opposingSide, Piece::$OCCUPIED);
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
		
		// TODO: e.p.
		
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