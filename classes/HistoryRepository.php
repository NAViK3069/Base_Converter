<?php
// classes/HistoryRepository.php
class HistoryRepository {

    public static function save(PDO $pdo, array $data): void {

        $th_now_iso = (new DateTime('now', new DateTimeZone('Asia/Bangkok')))->format(DateTime::ATOM);

        $stmt = $pdo->prepare("
            INSERT INTO problems (
                type, input_value, input_base, bit_width, op, second_value, target_base, result_value, created_at
            ) VALUES (
                :type, :input_value, :input_base, :bit_width, :op, :second_value, :target_base, :result_value, :created_at
            )
        ");
        $stmt->execute([
            ':type'         => $data['type']         ?? null,
            ':input_value'  => $data['input_value']  ?? null,
            ':input_base'   => $data['input_base']   ?? null,
            ':bit_width'    => $data['bit_width']    ?? null,
            ':op'           => $data['op']           ?? null,
            ':second_value' => $data['second_value'] ?? null,
            ':target_base'  => $data['target_base']  ?? null,
            ':result_value' => $data['result_value'] ?? null,
            ':created_at'   => $th_now_iso,
        ]);
    }

    public static function latest(PDO $pdo, int $limit = 50): array {
        $stmt = $pdo->prepare("SELECT * FROM problems ORDER BY id DESC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
