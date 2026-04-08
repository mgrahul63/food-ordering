<?php
session_start();
include('../config/constants.php');

$objDb = new DbConnect();
$pdo = $objDb->connect(); // PDO connection

if(isset($_GET['id'])){
    $id = $_GET['id'];

    // First, get the image name to delete the file
    $stmt_img = $pdo->prepare("SELECT image_name FROM tbl_category WHERE id=:id");
    $stmt_img->bindParam(':id', $id);
    $stmt_img->execute();
    $category = $stmt_img->fetch(PDO::FETCH_ASSOC);

    if($category && !empty($category['image_name'])){
        $image_path = "../images/category/".$category['image_name'];
        if(file_exists($image_path)){
            unlink($image_path); // Delete image from folder
        }
    }

    // Delete category from database
    $stmt = $pdo->prepare("DELETE FROM tbl_category WHERE id=:id");
    $stmt->bindParam(':id', $id);

    if($stmt->execute()){
        $_SESSION['delete'] = "<div class='alert alert-success'>Category deleted successfully.</div>";
    } else {
        $_SESSION['delete'] = "<div class='alert alert-danger'>Failed to delete category.</div>";
    }

    // Redirect back to manage-category page
    header('location: manage-category.php');
    exit();
} else {
    // No ID provided
    $_SESSION['delete'] = "<div class='alert alert-warning'>Unauthorized access.</div>";
    header('location: manage-category.php');
    exit();
}
?>
