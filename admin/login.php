<?php 
session_start(); // Make sure session is started
include('./partials/menu.php');   
include('../config/constants.php');  

$objDb = new DbConnect();
$pdo = $objDb->connect(); // PDO connection
?>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="card shadow p-4" style="width: 400px;">
            <h2 class="text-center mb-4">Admin Login</h2> 
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" id="username" placeholder="Enter Username" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter Password" required>
                </div>

                <div class="d-grid">
                    <input type="submit" name="submit" value="Login" class="btn btn-primary">
                </div>
            </form>

            <p class="text-center mt-3 small">Created By - <a href="https://www.facebook.com/profile.php?id=100049658165463" target="_blank">Al Imran</a></p>
        </div>
    </div>

    <?php 
    if(isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password']; // md5 if your DB stores it like this

        try {
            // Prepare the SQL statement
            $stmt = $pdo->prepare("SELECT * FROM tbl_admin WHERE username = :username AND password = :password");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);

            // Execute
            $stmt->execute();

            // Check if a row is returned
            if($stmt->rowCount() == 1) {
                $_SESSION['admin'] = $username;
                // Redirect to Dashboard/Home
                header('location: index.php');
                exit();
            } else {
                $_SESSION['login'] = "<div class='alert alert-danger text-center'>Username or Password did not match.</div>";
                header('location:'.SITEURL.'admin/login.php');
                exit();
            }

        } catch(PDOException $e) {
            echo "<div class='alert alert-danger'>Error: ".$e->getMessage()."</div>";
        }
    }
    ?>

<?php 
include('./partials/footer.php');
?>
