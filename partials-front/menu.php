<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('config/constants.php');

$objDb = new DbConnect();
$conn = $objDb->connect();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if($user_id){
    $sql = "SELECT image, name FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $imagePath = !empty($user['image']) ? './images/users/' . $user['image'] : './images/users/default.png';
    $name = htmlspecialchars($user['name']);
    $firstName = explode(' ', $name)[0]; // First word only
}
 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css">

    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
     <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
</head>
<body>

 
<!-- Navbar Section Starts Here -->
<section class="bg-secondary  shadow-md" style="background-color: #3F9B0C;">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        
        <!-- Logo -->
        <div class="logo">
            <a href="index.php" title="Logo" class="flex items-center justify-center">
                <img src="images/logo.png" alt="Restaurant Logo" class="h-10 w-auto"> <span>JKKNIU</span>
            </a>
        </div>

        <!-- Menu -->
        <div class="menu">
            <ul class="flex space-x-6 text-gray-700 font-medium items-center">
                <li><a href="index.php" class="hover:text-red-500 transition">Home</a></li>
                <li><a href="categories.php" class="hover:text-red-500 transition">Categories</a></li>
                <li><a href="foods.php" class="hover:text-red-500 transition">Foods</a></li>
                <li><a href="contact.php" class="hover:text-red-500 transition">Contact</a></li>
                 <li><a href="notice.php" class="hover:text-red-500 transition">Notice</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    
                    <li><a href="order.php" class="hover:text-red-500 transition">Order</a></li>
                    <details class="relative group">

                        <summary class="flex items-center space-x-2 cursor-pointer list-none">
                            <img 
                                src="<?= $imagePath ?>" 
                                alt="Profile" 
                                class="w-8 h-8 rounded-full border-2 border-gray-300 object-cover"
                                onerror="this.onerror=null; this.src='./images/users/default.png';"
                            />
                            <span class="text-sm text-gray-800 font-semibold">
                                <?= $firstName ?>
                            </span>

                            <svg 
                                class="w-4 h-4 text-gray-600" 
                                fill="none" 
                                stroke="currentColor" 
                                stroke-width="2" 
                                viewBox="0 0 24 24">
                                <path 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    d="M19 9l-7 7-7-7"/>
                            </svg>

                        </summary>

                        <ul class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-md z-50">
                            <li>
                            <a href="settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Settings</a>
                            </li>
                            <li>
                            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                            </li>
                        </ul>
                    </details>
                <?php else: ?>
                    <li><a href="login.php" class="hover:text-red-500 transition">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</section>
<!-- Navbar Section Ends Here -->
