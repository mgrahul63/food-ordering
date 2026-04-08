<?php
require_once('config/constants.php');

session_start();

$user_id = $_SESSION['user_id'];
$objDb = new DbConnect();
$conn = $objDb->connect();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

 
// Check if `id` is set in URL
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];

    try {
        // Verify order belongs to the user
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            ':id' => $order_id,
            ':user_id' => $user_id
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            if (strtolower($order['status']) !== 'cancelled') {
                // Update the order status
                $update = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = :id");
                $update->execute([':id' => $order_id]);
            }
        }

        // Redirect in all cases
        header("Location: order.php");
        exit;

    } catch (PDOException $e) {
        echo "<p class='text-red-600 text-center mt-6'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
} else {
    header("Location: order.php");
    exit;
}
?>
