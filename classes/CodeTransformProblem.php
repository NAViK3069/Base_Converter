<?php
require_once __DIR__ . '/Problem.php';

class CodeTransformProblem extends Problem {
    public function __construct(string $mode, string $input) {
        parent::__construct('CODE_TRANS', ['mode'=>$mode, 'input'=>$input]);
    }
    public function validate(): bool {
        return in_array($this->input['mode'], ['B2G','G2B','BCD2DEC','DEC2BCD','ASC2BIN','BIN2ASC'], true);
    }
}
