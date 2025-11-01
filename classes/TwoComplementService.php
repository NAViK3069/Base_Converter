<?php
// classes/TwoComplementService.php
class TwoComplementService {
    public static function encode(int $value, int $bitWidth): string {
        if ($bitWidth < 1 || $bitWidth > 32) throw new InvalidArgumentException('bitWidth 1..32');
        $mod = 1 << $bitWidth;
        if ($value < 0) $value = $mod + $value;
        $value &= ($mod - 1);
        return str_pad(decbin($value), $bitWidth, '0', STR_PAD_LEFT);
    }
}
