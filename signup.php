<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('config/constants.php');
$objDb = new DbConnect();
$conn = $objDb->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Plain text (not recommended for production)
    $contact = trim($_POST['contact']);

    if (empty($name) || empty($email) || empty($password) || empty($contact)) {
        echo "<div class='text-red-600 text-center mt-4'>All fields are required.</div>";
        exit;
    }

    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $originalName = basename($_FILES['image']['name']);
        $targetDir = './images/users/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetFile = $targetDir . $originalName;

        if (move_uploaded_file($imageTmp, $targetFile)) {
            $imageName = $originalName;
        } else {
            echo "<div class='text-red-600 text-center mt-4'>Image upload failed.</div>";
            exit;
        }
    }

    try {
        $sql = "INSERT INTO users (name, email, password, contact, image, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $email, $password, $contact, $imageName]);

        if ($stmt) {
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        echo "<div class='text-red-600 text-center mt-4'>Error: " . $e->getMessage() . "</div>";
    }
}
?>

<?php include('partials-front/menu.php'); ?>

<div class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-700">Create Account</h2>

        <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-medium mb-1">Name</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="email" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Contact</label>
                <input type="text" name="contact" required
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Profile Image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full text-sm border border-gray-300 px-3 py-2 rounded" />
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition duration-200">
                Sign Up
            </button>

            <p class="text-center text-sm mt-4">Already have an account?
                <a href="login.php" class="text-blue-600 hover:underline">Login</a>
            </p>
        </form>
    </div>
</div>

<?php include('partials-front/footer.php'); ?>