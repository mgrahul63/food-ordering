<?php
function getAvailableFood(PDO $pdo, int $foodId, int $totalFood): int {
    $statuses = ['Pending', 'Completed', 'Cancelled'];
    $totals = [
        'Pending' => 0,
        'Completed' => 0,
        'Cancelled' => 0
    ];

    foreach ($statuses as $status) {
        $stmt = $pdo->prepare("
            SELECT * FROM orders
            WHERE status = :status
            AND JSON_CONTAINS(food_ids, :id, '$')
        ");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', json_encode(strval($foodId)));
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $foodIds = json_decode($row['food_ids'], true);
            $quantities = json_decode($row['quantities'], true);

            $index = array_search(strval($foodId), $foodIds);
            if ($index !== false) {
                $totals[$status] += intval($quantities[$index]);
            }
        }
    }

    return ($totalFood + $totals['Cancelled']) - $totals['Pending'] - $totals['Completed'];
}
?>
