<?php

namespace Micaherne\Bitboards;

/**
 * Class for move-related functionality.
 *
 * NB: This is *not* the generally used method of passing moves around in
 * the program. The canonical representation of a move is an array of
 * (from square, to square, [promotion piece])
 *
 * @author Michael Aherne
 *
 */
class Move {

    public $from;
    public $to;
    public $promotionPiece;

    public function __construct(array $move) {
        $this->from = $move[0];
        $this->to   = $move[1];
        if (isset($move[2])) {
            $this->promotionPiece = $move[2];
        }
    }

    public function __toString() {
        $result = self::squareToAlgebraic($this->from) . self::squareToAlgebraic($this->to);
        if (!is_null($this->promotionPiece)) {
            $result = strtoupper(Piece::$fenAlias[$this->promotionPiece]);
        }
        return $result;
    }

    public static function squareToAlgebraic($square) {
        return chr(ord('a') + ($square % 8)) . (($square >> 3) + 1);
    }

}