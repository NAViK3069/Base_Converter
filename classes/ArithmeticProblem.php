<?php
require_once __DIR__ . '/Problem.php';

class ArithmeticProblem extends Problem {
    public function __construct(
        string $aValue, int $aBase, string $op,
        string $bValue, int $bBase, int $outBase,
        string $mode, int $bitWidth
    ) {
        parent::__construct('ARITHMETIC', [
            'a_value'=>$aValue, 'a_base'=>$aBase, 'op'=>$op,
            'b_value'=>$bValue, 'b_base'=>$bBase, 'out_base'=>$outBase,
            'mode'=>$mode, 'bit_width'=>$bitWidth
        ]);
    }

    public function validate(): bool {
        $i = $this->input;
        if (!in_array($i['a_base'], [2,8,10,16], true)) return false;
        if (!in_array($i['b_base'], [2,8,10,16], true)) return false;
        if (!in_array($i['out_base'], [2,8,10,16], true)) return false;
        if (!in_array($i['op'], ['+','-','*','/'], true)) return false;
        if (!in_array($i['mode'], ['normal','ones_sub','twos_sub'], true)) return false;
        return true;
    }
}
