<?php

namespace Micaherne\Bitboards;

use Micaherne\Bitboards\Piece;
use Micaherne\Bitboards\Exception\FENException;

class Position {

	// Array of BitBoardPieces objects for each side's pieces
	public $colour = array(null, null);

	// 64 item array of squares
	public $board = array();

	public $materialEvaluation;
	public $kingSquare = array(null, null);

	public $whiteToMove = true;

	public $castling = array(3, 3); // KQ

	public $ep; // Bitboard
	public $halfmove = 0;
	public $fullmove = 0;

	// Stack of undo data created by move()
	public $undoStack = array();

	public static $START_POSITION = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

	public function __construct() {
		for($side = Piece::$BLACK; $side <= Piece::$WHITE; $side++) {
			$this->colour[$side] = new BitBoardPieces();
		}
		$this->clearBoard();
	}

	public function clearBoard() {
		$this->board = array();
		for ($i = 0; $i < 64; $i++) {
			$this->board[$i] = Piece::$BLANK;
		}
	}

	public static function squareIndex($file, $rank) {
		return $rank * 8 + $file;
	}

	public function getBoardPiece($index) {
		return $this->board[$index];
	}

	public function move($move) {
		$sideToMove = $whiteToMove ? Piece::$WHITE : Piece::$BLACK;
		$undo = array('move' => $move,
			'movedPiece' => $this->board[$move[0]], // used for pawn promotions
			'capturedPiece' => $this->board[$move[1]],
			'ep' => $this->ep,
			'castling' => $this->castling[$sideToMove],
			'halfmove' => $this->halfmove
		);
		$this->undoStack[] = $undo;

	}

	/**
	 * Return a visual representation of the board.
	 *
	 * @return string
	 */
	public function __toString() {
		$result = $sep = "+-+-+-+-+-+-+-+-+\n";
		for ($rank = 7; $rank >= 0; $rank--) {
			$result .= "|";
			for ($file = 0; $file < 8; $file++) {
				$result .= Piece::$fenAlias[$this->board[self::squareIndex($file, $rank)]];
				$result .= "|";
			}
			$result .= "\n$sep";
		}
		return $result;
	}

	public function startPosition() {
		return $this->fromFEN(self::$START_POSITION);
	}

	public function fromFEN($fen, $validate = false) {
		$this->clearBoard();
		$parts = preg_split('/\s+/', $fen);

		if ($validate && count($parts) < 4) {
			throw new FENException("FEN must have at least 4 parts");
		}

		$lines = explode('/', $parts[0]);
		if ($validate && count($lines) != 8) {
			throw new FENException("Board representation must have 8 parts");
		}

		$rank = 7;
		foreach ($lines as $line) {
			$file = 0;
			foreach (str_split($line) as $char) {
				if (is_numeric($char)) {
					$file += $char;
				} else {
					if ($validate && !array_key_exists($char, Piece::$fenAlias)) {
						throw new FENException("Invalid piece type $char");
					}
					$this->board[self::squareIndex($file, $rank)] = Piece::$fenAlias[$char];
					$file++;
				}
			}
			$rank--;
		}

		if ($parts[1] == 'w') {
			$this->whiteToMove = true;
		} else if ($parts[1] == 'b') {
			$this->whiteToMove == false;
		} else if ($validate) {
			throw new FENException("Invalid side to move {$parts[1]}");
		}

		$this->castling = array(0, 0);
		if ($parts[2] != '-') {
			foreach(str_split($parts[2]) as $char) {
				switch($char) {
				    case 'K':
				    	$this->castling[Piece::$WHITE] += 2;
				    	break;
				    case 'Q':
				    	$this->castling[Piece::$WHITE] += 1;
				    	break;
				    case 'k':
				    	$this->castling[Piece::$BLACK] += 2;
				    	break;
				    case 'q':
				    	$this->castling[Piece::$BLACK] += 1;
				    	break;
				    default:
				    	throw new FENException("Invalid castling type $char");
				}
			}
		}

		$this->ep = new BitBoard();
		if ($parts[3] != '-') {
			$file = ord($parts[3]{0}) - ord("a");
			$rank = $parts[3]{1} - 1;
			$this->ep->setBit(self::squareIndex($file, $rank));
		}

		$this->halfmove = $parts[4];
		$this->fullmove = $parts[5];

		$this->setBitboards();
	}

	public function setBitboards() {
		// Empty them first
		for($side = Piece::$BLACK; $side <= Piece::$WHITE; $side++) {
			for($piece = Piece::$BLANK; $piece <= Piece::$KING; $piece++) {
				$this->colour[$side]->setPieceBitboard($piece, new BitBoard());
			}
		}

		// Go through the squares and update
		for ($square = 0; $square < 64; $square++) {
			if ($this->board[$square] == Piece::$BLANK) {
				continue;
			}
			$piece = $this->board[$square];
			$side = ($piece > 0) ? 1 : 0;
			$this->colour[$side]->getPieceBitboard(abs($piece))->setBit($square);
			$this->colour[$side]->getPieceBitboard(0)->setBit($square);
		}
	}

	public function getPieceBitboard($side, $piece) {
		return $this->colour[$side]->getPieceBitboard($piece);
	}
}