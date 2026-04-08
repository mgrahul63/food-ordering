
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Food Order Website - Home Page</title>

    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional: Your custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body> 
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Food Order</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link" href="manage-category.php">Category</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-food.php">Food</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-order.php">Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="complaint.php">Complaint</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notice.php">Notice</a>
                    </li>
                     <li class="nav-item">
                        <!-- <a class="nav-link" href="manage-admin.php">Admin Profile</a> -->
                    </li>
                    <li class="nav-item">
                        <?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                            <a class="nav-link" href="logout.php">Logout</a>
                        <?php else: ?>
                            <a class="nav-link" href="login.php">Login</a>
                        <?php endif; ?>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Menu Section Ends -->

 
