<?php

namespace Micaherne\Bitboards;

class BitBoardPieces {
	
	/**
	 * The piece positions on a board for a given colour.
	 * 
	 * Index 0 is all occupied squares (by this colour), the others
	 * are the occupied square by that piece type.
	 *  
	 * @var unknown
	 */
	private $pieces = array(null, null, null, null, null, null, null);
	
	public function setPieceBitboard($pieceType, BitBoard $bitboard) {
		$this->pieces[$pieceType] = $bitboard;
	}
	
	public function getPieceBitboard($pieceType) {
		return $this->pieces[$pieceType];
	}
			
}