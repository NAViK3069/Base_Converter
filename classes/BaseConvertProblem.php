<?php
require_once __DIR__ . '/Problem.php';
require_once __DIR__ . '/NumberValue.php';

class BaseConvertProblem extends Problem {
    public function __construct(string $value, int $fromBase, int $toBase, ?int $bitWidth = null) {
        parent::__construct('BASE_CONVERT', [
            'input_value' => $value,
            'input_base'  => $fromBase,
            'target_base' => $toBase,
            'bit_width'   => $bitWidth
        ]);
    }
    public function validate(): bool {
        $nv = new NumberValue($this->input['input_value'], (int)$this->input['input_base'], $this->input['bit_width']);
        if (!in_array((int)$this->input['target_base'], [2,8,10,16], true)) return false;
        return $nv->validate();
    }
}
