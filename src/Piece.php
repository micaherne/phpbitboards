<?php

namespace Micaherne\Bitboards;

class Piece {
	public static $BLANK = 0;
	public static $OCCUPIED = 0;
	public static $PAWN = 1;
	public static $KNIGHT = 2;
	public static $BISHOP = 3;
	public static $ROOK = 4;
	public static $QUEEN = 5;
	public static $KING = 6;

	public static $BLACK = 0;
	public static $WHITE = 1;

	/*
	 * A two-way array of piece number to string representation
	 */
	public static $fenAlias = array(
			'P' => 1,
			'N' => 2,
			'B' => 3,
			'R' => 4,
			'Q' => 5,
			'K' => 6,
			'p' => -1,
			'n' => -2,
			'b' => -3,
			'r' => -4,
			'q' => -5,
			'k' => -6,
			0 => ' ',
			1 => 'P',
			2 => 'N',
			3 => 'B',
			4 => 'R',
			5 => 'Q',
			6 => 'K',
			-1 => 'p',
			-2 => 'n',
			-3 => 'b',
			-4 => 'r',
			-5 => 'q',
			-6 => 'k'
	);

	public static $promotionPieces = array(
	    5, 4, 3, 2 // queen, rook, knight, bishop
	);
}