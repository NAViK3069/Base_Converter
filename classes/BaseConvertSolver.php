<?php
require_once __DIR__ . '/ProblemSolver.php';
require_once __DIR__ . '/BaseConvertProblem.php';
require_once __DIR__ . '/NumberValue.php';
require_once __DIR__ . '/HistoryRepository.php';

class BaseConvertSolver extends ProblemSolver {
    public function getOutput(PDO $pdo) {
        /** @var BaseConvertProblem $p */
        $p = $this->problem;
        if (!$p->validate()) throw new InvalidArgumentException('รูปแบบข้อมูลไม่ถูกต้อง');

        $in  = $p->getInput();
        $nv  = new NumberValue($in['input_value'], (int)$in['input_base'], $in['bit_width']);
        $dec = $nv->toDecimal();
        $out = NumberValue::fromDecimal($dec, (int)$in['target_base'], ($in['target_base']==2?$nv->getBitWidth():null));

        HistoryRepository::save($pdo, [
            'type'         => 'BASE_CONVERT',
            'input_value'  => $in['input_value'],
            'input_base'   => (int)$in['input_base'],
            'bit_width'    => $nv->getBitWidth(),
            'target_base'  => (int)$in['target_base'],
            'result_value' => $out
        ]);

        $steps = [
            "อ่านอินพุตเป็นฐาน {$in['input_base']}",
            "แปลงเป็นฐานสิบ = $dec",
            "แปลงไปฐาน {$in['target_base']} = $out",
        ];
        if ($nv->getBitWidth() && (int)$in['target_base']===2) {
            $steps[] = "จัดรูปแบบไบนารีให้ยาว {$nv->getBitWidth()} บิต (padding)";
        }
        return [$out, $steps];
    }
}
