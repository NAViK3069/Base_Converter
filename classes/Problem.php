<?php
// classes/Problem.php (Abstract)
abstract class Problem {
    protected string $type;
    protected array $input;

    public function __construct(string $type, array $input) {
        $this->type = $type;
        $this->input = $input;
    }
    public function getType(): string { return $this->type; }
    public function getInput(): array { return $this->input; }

    abstract public function validate(): bool;
}
