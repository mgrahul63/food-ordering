<?php 
session_start(); // Make sure session is started
include('./partials/menu.php');   
include('../config/constants.php');  

include('./partials/login-check.php');

$objDb = new DbConnect();
$pdo = $objDb->connect(); // PDO connection
?>

<!-- Cover / Banner Section -->
<div class="container-fluid p-0">
    <div class="position-relative text-center text-white">
        <img src="../images/coverImage/cover.png" class="img-fluid w-100" alt="Admin Banner" style="max-height:500px; object-fit:cover;">
        <div class="position-absolute top-50 start-50 translate-middle">
            <h1 class="display-4 fw-bold" style="color:orange">Welcome to Admin Panel</h1>
            <?php if(isset($_SESSION['user'])): ?>
                <p class="lead text-dark">Hello, <?php echo htmlspecialchars($_SESSION['user']); ?>!</p>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Main Metrics Section -->
<div class="container my-5">
    
    <div class="row text-center g-4">

        <!-- Categories Card -->
         <!-- <div class="col-md-3">
            <div class="card shadow h-100 border-primary">
                <div class="card-body">
                    <?php 
                        $stmt = $pdo->query("SELECT COUNT(*) AS count FROM tbl_category");
                        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                    ?>
                    <i class="bi bi-folder-fill fs-1 text-primary mb-2"></i>
                    <h2 class="card-title"><?php echo $count; ?></h2>
                    <p class="card-text">Categories</p>
                </div>
            </div>
        </div> -->

        <!-- Foods Card -->
         <?php
            // Fetch order counts
            $stmt = $pdo->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(category_name = 'Breakfast') AS Breakfast,
                    SUM(category_name = 'Lunch') AS Lunch,
                    SUM(category_name = 'Dinner') AS Dinner
                FROM tbl_food
            ");
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalfood      = $counts['total'];
            $Breakfast    = $counts['Breakfast'];
            $Lunch  = $counts['Lunch'];
            $Dinner  = $counts['Dinner'];
        ?>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 p-3">
                <h5 class="card-title text-center mb-3">
                    <i class="bi bi-bag-fill text-warning me-2"></i>Foods Overview
                </h5>

                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr class="fw-bold">
                            <td class="text-start">Total Foods</td>
                            <td><?= $totalfood ?></td>
                            <td><a href="manage-food.php" class="text-decoration-none small">View</a></td>
                        </tr>

                        <tr>
                            <td class="text-start">Breakfast</td>
                            <td class="fw-bold text-success"><?= $Breakfast ?></td>
                            <td><a href="manage-food.php?status=Breakfast" class="text-decoration-none small">View</a></td>
                        </tr>
                        <tr>
                            <td class="text-start">Lunch</td>
                            <td class="fw-bold text-primary"><?= $Lunch ?></td>
                            <td><a href="manage-food.php?status=Lunch" class="text-decoration-none small">View</a></td>
                        </tr>
                        <tr>
                            <td class="text-start">Dinner</td>
                            <td class="fw-bold text-danger"><?= $Dinner ?></td>
                            <td><a href="manage-food.php?status=Dinner" class="text-decoration-none small">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Card -->
        <?php
            // Fetch order counts
            $stmt = $pdo->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(status = 'Pending') AS pending,
                    SUM(status = 'Cancelled') AS cancelled,
                    SUM(status = 'Completed') AS completed
                FROM orders
            ");
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);

            $total      = $counts['total'];
            $pending    = $counts['pending'];
            $cancelled  = $counts['cancelled'];
            $completed  = $counts['completed'];
        ?>
        <div class="col-md-3">
            <div class="card shadow-sm h-100 p-3">
                <h5 class="card-title text-center mb-3">
                    <i class="bi bi-bag-fill text-warning me-2"></i>Orders Overview
                </h5>

                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr class="fw-bold">
                            <td class="text-start">Total Orders</td>
                            <td class="fw-bold"><?= $total ?></td>
                            <td><a href="manage-order.php" class="text-decoration-none small">View</a></td>
                        </tr>
                        <tr>
                            <td class="text-start">Completed</td>
                            <td class="fw-bold text-success"><?= $completed ?></td>
                            <td><a href="manage-order.php?status=Completed" class="text-decoration-none small">View</a></td>
                        </tr>
                        <tr>
                            <td class="text-start">Pending</td>
                            <td class="fw-bold text-primary"><?= $pending ?></td>
                            <td><a href="manage-order.php?status=Pending" class="text-decoration-none small">View</a></td>
                        </tr>
                        <tr>
                            <td class="text-start">Cancelled</td>
                            <td class="fw-bold text-danger"><?= $cancelled ?></td>
                            <td><a href="manage-order.php?status=Cancelled" class="text-decoration-none small">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Revenue Card -->
        <div class="col-md-3">
            <div class="card shadow h-100 border-danger">
                <div class="card-body">
                    <?php 
                    $stmt = $pdo->query("SELECT SUM(totalPrice) AS totalRevenue FROM orders WHERE status='Completed'");
                    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['totalRevenue'] ?? 0;
                    ?>

                    <i class="bi bi-currency-dollar fs-1 text-danger mb-2"></i>
                    <h2 class="card-title">$<?= number_format($total_revenue, 2) ?></h2>
                    <p class="card-text">Revenue Generated</p>
                    <a href="manage-order.php?status=Completed" class="btn btn-sm btn-danger mt-2">View Completed Orders</a>
                    
                </div>
            </div>
        </div>


    </div>
</div>

<?php include('partials/footer.php'); ?>
