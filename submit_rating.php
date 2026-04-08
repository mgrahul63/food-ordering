<?php
session_start();
require_once('config/constants.php');

header('Content-Type: application/json');

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit;
}

// Get POST data (JSON)
$data = json_decode(file_get_contents('php://input'), true);

$order_id  = intval($data['order_id'] ?? 0);
$rating_id = intval($data['rating_id'] ?? 0);
$rating    = isset($data['rating']) ? intval($data['rating']) : null; // null if not sent
$review    = isset($data['review']) ? trim($data['review']) : null;    // null if not sent

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    $objDb = new DbConnect();
    $conn = $objDb->connect();

    // ----------------------
    // Update existing rating
    // ----------------------
    if ($rating_id > 0) {
        $fields = [];
        $params = [':rating_id' => $rating_id, ':user_id' => $user_id];

        if ($rating !== null && $rating > 0) {
            $fields[] = "rating = :rating";
            $params[':rating'] = $rating;
        }

        if ($review !== null && $review !== '') {
            $fields[] = "review = :review";
            $params[':review'] = $review;
        }

        if (!empty($fields)) {
            $sql = "UPDATE ratings 
                    SET " . implode(', ', $fields) . ",  updated_at = NOW()
                    WHERE id = :rating_id AND user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // ----------------------
    // Insert new rating(s)
    // ----------------------
    $stmt = $conn->prepare("
        SELECT food_ids 
        FROM orders 
        WHERE id = :order_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':order_id' => $order_id,
        ':user_id'  => $user_id
    ]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $food_ids = json_decode($order['food_ids'], true) ?: [];

    foreach ($food_ids as $food_id) {
        $stmt = $conn->prepare("
            INSERT INTO ratings (user_id, order_id, food_id, rating, review, created_at)
            VALUES (:user_id, :order_id, :food_id, :rating, :review, NOW())
            ON DUPLICATE KEY UPDATE
                rating = IF(:rating IS NOT NULL, VALUES(rating), rating),
                review = IF(:review IS NOT NULL, VALUES(review), review)
        ");

        $stmt->execute([
            ':user_id'  => $user_id,
            ':order_id' => $order_id,
            ':food_id'  => $food_id,
            ':rating'   => $rating,
            ':review'   => $review
        ]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
