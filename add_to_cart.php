<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? 'index.php'; // Save the last visited page
    $_SESSION['pending_food_id'] = $_POST['food_id'];
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if (!isset($_SESSION['selected_foods'])) {
    $_SESSION['selected_foods'] = [];
}


if (isset($_POST['food_id']) || $_SESSION['pending_food_id']){
    $foodId = $_POST['food_id'] ?? $_SESSION['pending_food_id'];
    if (!in_array($foodId, $_SESSION['selected_foods'])) {
        $_SESSION['selected_foods'][] = $foodId;
    } 
    if(isset($_SESSION['pending_food_id'])){
        unset($_SESSION['pending_food_id']);  
        header("Location: order.php"); // Redirect to order page after adding food
        exit();
    }
     
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No food ID']);
}
?>
