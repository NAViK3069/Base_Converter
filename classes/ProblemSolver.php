<?php
// classes/ProblemSolver.php (Abstract)
abstract class ProblemSolver {
    protected Problem $problem;
    public function __construct(Problem $problem) {
        $this->problem = $problem;
    }

    abstract public function getOutput(PDO $pdo);
}
