<?php
session_start();
include('../config/constants.php');

$objDb = new DbConnect();
$pdo = $objDb->connect();

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // Get image to delete file
    $stmt_img = $pdo->prepare("SELECT image_path FROM tbl_food WHERE id=:id");
    $stmt_img->bindParam(':id', $id);
    $stmt_img->execute();
    $food = $stmt_img->fetch(PDO::FETCH_ASSOC);

    if($food && $food['image_path']!='' && $food['image_path']!='default.png'){
        $path = "../images/food/".$food['image_path'];
        if(file_exists($path)) unlink($path);
    }

    // Delete from DB
    $stmt = $pdo->prepare("DELETE FROM tbl_food WHERE id=:id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $_SESSION['delete'] = "<div class='alert alert-success'>Food deleted successfully.</div>";
    header('location: manage-food.php');
    exit();
} else {
    $_SESSION['delete'] = "<div class='alert alert-warning'>Unauthorized access.</div>";
    header('location: manage-food.php');
    exit();
}
?>
