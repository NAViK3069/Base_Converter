<?php
// classes/NumberValue.php
class NumberValue {
    private string $value;
    private int $base;
    private ?int $bitWidth;

    public function __construct(string $value, int $base, ?int $bitWidth = null) {
        $this->value = strtoupper(trim($value));
        $this->base = $base;
        $this->bitWidth = $bitWidth;
    }

    public function getValue(): string { return $this->value; }
    public function getBase(): int { return $this->base; }
    public function getBitWidth(): ?int { return $this->bitWidth; }

    public function validate(): bool {
        $p = match ($this->base) {
            2  => '/^[01]+$/',
            8  => '/^[0-7]+$/',
            10 => '/^[+-]?[0-9]+$/',
            16 => '/^[0-9A-F]+$/',
            default => null
        };
        return $p ? (bool)preg_match($p, $this->value) : false;
    }

    public function toDecimal(): int {
        return ($this->base === 10) ? (int)$this->value : intval($this->value, $this->base);
    }

    public static function fromDecimal(int $dec, int $targetBase, ?int $bitWidth = null): string {
        $out = match ($targetBase) {
            2  => decbin($dec),
            8  => decoct($dec),
            10 => (string)$dec,
            16 => strtoupper(dechex($dec)),
            default => throw new InvalidArgumentException('Unsupported base')
        };
        if ($targetBase === 2 && $bitWidth && $bitWidth > 0) {
            $out = substr(str_pad($out, $bitWidth, '0', STR_PAD_LEFT), -$bitWidth);
        }
        return $out;
    }
}
