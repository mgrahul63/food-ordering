<?php
session_start();
require_once('config/constants.php'); // your DB connection is in here

$objDb = new DbConnect();
$conn = $objDb->connect();

// Ensure food selections and quantities are present
if (!isset($_SESSION['selected_foods']) || !isset($_SESSION['quantities'])) {
    echo "Food selection or quantities missing.";
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$selected_foods = $_SESSION['selected_foods']; // e.g., [1, 2, 3]
$quantities = $_SESSION['quantities'];        // e.g., ['1' => 2, '2' => 1, '3' => 5]

if (!$user_id || !is_array($selected_foods) || !is_array($quantities)) {
    echo "Invalid input.";
    exit;
}

// Fetch user info
$name = $email = $contact = '';
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $name = $user['name'];
    $email = $user['email'];
    $contact = $user['contact'];
}

// Prepare food IDs and quantities
$food_ids = array_values($selected_foods);
$quantities_list = [];
$prices = [];
$totalPrice = 0;

foreach ($food_ids as $id) {
    $qty = (int)($quantities[$id] ?? 1);
    $quantities_list[] = $qty;

    // Get food price
    $food_stmt = $conn->prepare("SELECT price FROM tbl_food WHERE id = :foodId");
    $food_stmt->bindParam(':foodId', $id, PDO::PARAM_INT);
    $food_stmt->execute();
    $singleFood = $food_stmt->fetch(PDO::FETCH_ASSOC);

    if ($singleFood) {
        $prices[] = $singleFood['price'];
        $totalPrice += $singleFood['price'] * $qty;
    }
}

// Encode arrays for storage
$food_ids_json = json_encode($food_ids);
$quantities_json = json_encode($quantities_list);
$prices_json = json_encode($prices);

try {
    $sql = "INSERT INTO orders 
            (user_id, user_name, user_email, user_contact, food_ids, quantities, prices, totalPrice) 
            VALUES 
            (:user_id, :user_name, :user_email, :user_contact, :food_ids, :quantities, :prices, :totalPrice)";
    
    $stmt = $conn->prepare($sql); 
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_name', $name);
    $stmt->bindParam(':user_email', $email);
    $stmt->bindParam(':user_contact', $contact);
    $stmt->bindParam(':food_ids', $food_ids_json);
    $stmt->bindParam(':quantities', $quantities_json);
    $stmt->bindParam(':prices', $prices_json);
    $stmt->bindParam(':totalPrice', $totalPrice);
    $stmt->execute();

    // Clear session values after order
    unset($_SESSION['selected_foods']);
    unset($_SESSION['quantities']);

    echo "<script>
            alert('Order placed successfully!');
            window.location.href = 'order.php';
          </script>";
    exit;
} catch (PDOException $e) {
    echo "Error inserting order: " . $e->getMessage();
}
