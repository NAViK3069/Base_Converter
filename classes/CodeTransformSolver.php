<?php
require_once __DIR__ . '/ProblemSolver.php';
require_once __DIR__ . '/CodeTransformProblem.php';
require_once __DIR__ . '/HistoryRepository.php';

class CodeTransformSolver extends ProblemSolver {

    /* ---------- helpers: no-regex validators ---------- */
    private static function isBinary(string $s): bool {
        if ($s === '') return false;
        for ($i=0, $n=strlen($s); $i<$n; $i++) {
            $c = $s[$i];
            if ($c !== '0' && $c !== '1') return false;
        }
        return true;
    }
    private static function isBinaryGroupOf(string $s, int $bits): bool {
        // คั่นด้วยช่องว่างได้ เช่น "01000001 01000010"
        $parts = array_values(array_filter(explode(' ', trim($s)), fn($x)=>$x!==''));
        if (!$parts) return false;
        foreach ($parts as $p) {
            if (strlen($p) !== $bits) return false;
            if (!self::isBinary($p)) return false;
        }
        return true;
    }
    private static function isDecimal(string $s): bool {
        if ($s === '') return false;
        for ($i=0, $n=strlen($s); $i<$n; $i++) {
            if ($s[$i] < '0' || $s[$i] > '9') return false;
        }
        return true;
    }
    private static function splitSpaces(string $s): array {

        $s = trim(preg_replace("/[ \t\r\n]+/", " ", $s));
        return $s === '' ? [] : explode(' ', $s);
    }

    private static function binToGray(string $bin): string {
        $bin = str_replace([' ', "\t", "\r", "\n"], '', $bin);
        if (!self::isBinary($bin)) throw new InvalidArgumentException('ต้องเป็น Binary เท่านั้น');
        $g = $bin[0];
        for ($i=1, $n=strlen($bin); $i<$n; $i++) {
            $g .= ((int)$bin[$i-1] ^ (int)$bin[$i]);
        }
        return $g;
    }

    private static function grayToBin(string $gray): string {
        $gray = str_replace([' ', "\t", "\r", "\n"], '', $gray);
        if (!self::isBinary($gray)) throw new InvalidArgumentException('ต้องเป็น Gray/Binary เท่านั้น');
        $b = $gray[0];
        for ($i=1, $n=strlen($gray); $i<$n; $i++) {
            $b .= ((int)$b[$i-1] ^ (int)$gray[$i]);
        }
        return $b;
    }

    private static function decToBCD(string $dec): string {
        if (!self::isDecimal($dec)) throw new InvalidArgumentException('ต้องเป็นเลขฐานสิบ');
        $chunks = [];
        for ($i=0,$n=strlen($dec); $i<$n; $i++) {
            $v = ord($dec[$i]) - 48;            // 0..9
            $chunks[] = str_pad(decbin($v), 4, '0', STR_PAD_LEFT);
        }
        return implode(' ', $chunks);
    }

    private static function bcdToDec(string $bcd): string {
        $groups = self::splitSpaces($bcd);
        if (!$groups) throw new InvalidArgumentException('ใส่ BCD เป็นกลุ่ม 4 บิต คั่นช่องว่าง');
        $digits = [];
        foreach ($groups as $g) {
            if (strlen($g) !== 4 || !self::isBinary($g)) {
                throw new InvalidArgumentException('แต่ละกลุ่ม BCD ต้องยาว 4 บิต และเป็น 0/1 เท่านั้น');
            }
            $v = bindec($g);
            if ($v > 9) throw new InvalidArgumentException('มีกลุ่ม BCD ที่มากกว่า 1001 (9)');
            $digits[] = (string)$v;
        }
        $out = ltrim(implode('', $digits), '0');
        return $out === '' ? '0' : $out;
    }

    private static function ascToBin(string $s): string {
        $out = [];
        for ($i=0,$n=strlen($s); $i<$n; $i++) {
            $out[] = str_pad(decbin(ord($s[$i])), 8, '0', STR_PAD_LEFT);
        }
        return implode(' ', $out);
    }

    private static function binToAsc(string $bins): string {
        if (!self::isBinaryGroupOf($bins, 8)) {
            throw new InvalidArgumentException('ต้องเป็น Binary 8 บิต คั่นด้วยช่องว่าง เช่น 01001000 01001001');
        }
        $out = '';
        foreach (self::splitSpaces($bins) as $p) {
            $out .= chr(bindec($p));
        }
        return $out;
    }

    public function getOutput(PDO $pdo) {
        /** @var CodeTransformProblem $p */
        $p = $this->problem;
        if (!$p->validate()) throw new InvalidArgumentException('โหมดไม่ถูกต้อง');

        $m = $p->getInput()['mode'];
        $x = $p->getInput()['input'];

        $result = '';
        $steps  = [];

        switch ($m) {
            case 'B2G':
                $steps[] = 'Gray = Binary ⊕ (Binary เลื่อนขวา 1 บิตทีละหลัก)';
                $result  = self::binToGray($x);
                break;
            case 'G2B':
                $steps[] = 'บิตแรกเท่ากัน, บิตถัดไปคำนวณด้วย XOR สะสม';
                $result  = self::grayToBin($x);
                break;
            case 'DEC2BCD':
                $steps[] = 'แปลงตัวเลขฐาน 10 แต่ละหลัก → 4 บิต BCD';
                $result  = self::decToBCD($x);
                break;
            case 'BCD2DEC':
                $steps[] = 'อ่านทีละ 4 บิต (0..9) ต่อกันเป็นเลขฐาน 10';
                $result  = self::bcdToDec($x);
                break;
            case 'ASC2BIN':
                $steps[] = 'อักขระแต่ละตัว → รหัส ASCII 8 บิต';
                $result  = self::ascToBin($x);
                break;
            case 'BIN2ASC':
                $steps[] = 'ตัดเป็นกลุ่ม 8 บิต แล้วแปลงกลับเป็นอักขระ';
                $result  = self::binToAsc($x);
                break;
        }

        HistoryRepository::save($pdo, [
            'type' => 'CODE_TRANS',
            'op'   => $m,
            'input_value'  => $x,
            'result_value' => $result
        ]);

        return [$result, $steps];
    }
}
