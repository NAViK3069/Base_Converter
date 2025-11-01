<?php
require_once __DIR__ . '/ProblemSolver.php';
require_once __DIR__ . '/ArithmeticProblem.php';
require_once __DIR__ . '/NumberValue.php';
require_once __DIR__ . '/TwoComplementService.php';
require_once __DIR__ . '/HistoryRepository.php';

class ArithmeticSolver extends ProblemSolver {
    private static function toDec(string $v, int $b): int {
        return $b===10 ? (int)$v : intval($v, $b);
    }

    public function getOutput(PDO $pdo) {
        /** @var ArithmeticProblem $p */
        $p = $this->problem;
        if (!$p->validate()) throw new InvalidArgumentException('ข้อมูลไม่ถูกต้อง');
        $i = $p->getInput();

        $a   = self::toDec($i['a_value'], (int)$i['a_base']);
        $b   = self::toDec($i['b_value'], (int)$i['b_base']);
        $op  = $i['op'];
        $mode= $i['mode'];
        $bw  = (int)$i['bit_width'];
        $out = (int)$i['out_base'];

        $steps = [];
        $steps[] = "แปลง A→dec=$a, B→dec=$b";

        if ($mode === 'normal') {
            $dec = match ($op) {
                '+' => $a + $b,
                '-' => $a - $b,
                '*' => $a * $b,
                '/' => ($b===0 ? throw new InvalidArgumentException('หารด้วยศูนย์ไม่ได้') : intdiv($a,$b)),
            };
            $steps[] = "คำนวณฐานสิบ: $a $op $b = $dec";
        } else {
            $mask = ($bw >= 31) ? -1 : ((1 << $bw) - 1);
            $a2   = $a & $mask;
            if ($mode === 'ones_sub') {
                $bOnes = (~$b) & $mask;
                $dec   = ($a2 + $bOnes + 1) & $mask;
                $steps[] = "one’s(B) = " . NumberValue::fromDecimal($bOnes,2,$bw);
                $steps[] = "A + one’s(B) + 1 = " . NumberValue::fromDecimal($dec,2,$bw);
            } else {
                $bTwos = ((~$b) + 1) & $mask;
                $dec   = ($a2 + $bTwos) & $mask;
                $steps[] = "two’s(B) = " . NumberValue::fromDecimal($bTwos,2,$bw);
                $steps[] = "A + two’s(B) = " . NumberValue::fromDecimal($dec,2,$bw);
            }
            $signed = ($dec & (1<<($bw-1))) ? $dec - (1<<$bw) : $dec;
            $steps[] = "ตีความแบบ signed ($bw บิต) = $signed";
        }

        $result = NumberValue::fromDecimal($dec, $out, ($out===2)?$bw:null);
        $steps[] = "แปลงผลไปฐาน $out → $result";

        HistoryRepository::save($pdo, [
            'type'         => 'ARITHMETIC',
            'input_value'  => "{$i['a_value']} {$op} {$i['b_value']}",
            'input_base'   => (int)$i['a_base'],
            'second_value' => $i['b_value'],
            'target_base'  => $out,
            'bit_width'    => $bw,
            'result_value' => $result
        ]);

        return [$result, $steps];
    }
}
