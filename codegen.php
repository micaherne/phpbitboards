<?php

namespace Micaherne\Bitboards;

require_once(__DIR__.'/vendor/autoload.php');

MagicBitBoards::init();

$out = fopen('temp.php', 'w');

fwrite($out, '<?php

namespace Micaherne\Bitboards;

class MagicsPrecalculated {
        
    public $occupancyMaskRook;
	public $occupancyMaskBishop;
	
	public $occupancyVariation;
	public $occupancyAttackSet;
	
	public $magicNumberRook;
	public $magicNumberShiftsRook;
	public $magicNumberBishop;
	public $magicNumberShiftsBishop;
	
	public $magicMovesRook;
	public $magicMovesBishop;
        
    public function __construct() {
        
');

$pubvarsscalar = array('magicNumberShiftsRook',
    'magicNumberShiftsBishop');
foreach($pubvarsscalar as $var) {
    fwrite($out,  '$this->'.$var.' = array(');
    foreach (MagicBitBoards::$$var as $key =>$item) {
        fwrite($out,  $key.' => '.$item.',');
    }
    fwrite($out,  ');');
}

$pubvarssingle = array('occupancyMaskRook', 'occupancyMaskBishop', 'magicNumberBishop', 'magicNumberRook');

foreach($pubvarssingle as $var) {
    fwrite($out,  '$this->'.$var.' = array(');
    foreach (MagicBitBoards::$$var as $key =>$item) {
        fwrite($out,  $key.' => new BitBoard('.$item->getA().','.$item->getB().'),');
    }
    fwrite($out,  ');');
}

$pubvarsmultiple = array('occupancyVariation', 'occupancyAttackSet', 'magicMovesRook', 'magicMovesBishop');
foreach($pubvarsmultiple as $var) {
    fwrite($out,  '$this->'.$var.' = array();');
    foreach (MagicBitBoards::$$var as $key => $set) {
        fwrite($out,  '$this->'.$var.'['.$key.'] = array(');
        foreach($set as $key2 => $item) {
            fwrite($out,  $key2.' => new BitBoard('.$item->getA().','.$item->getB().'),');
        }
        fwrite($out,  ');');
    }
}
fwrite($out,  "} }");

