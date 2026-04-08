<?php
session_start();
 
require_once('config/constants.php');

// Connect to database using PDO
$objDb = new DbConnect();
$conn = $objDb->connect();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Email and Password are required.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // Redirect back if came from a protected page
                if (isset($_SESSION['redirect_to'])) {
                    $redirectPage = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: $redirectPage");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                $errors[] = "Invalid credentials.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include('partials-front/menu.php'); ?>

<div class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Login</h2>

        <?php if (!empty($errors)) : ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4 max-w-md mx-auto p-6 bg-white rounded shadow">
            <div>
                <label for="email" class="block text-gray-700 mb-1 font-medium">Email</label>
                <input
                type="email"
                name="email"
                id="email"
                required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                />
            </div>

            <div>
                <label for="password" class="block text-gray-700 mb-1 font-medium">Password</label>
                <input
                type="password"
                name="password"
                id="password"
                required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
                />
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition duration-200"
            >
                Login
            </button>

            <a
                href="signup.php"
                class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-blue-600 font-semibold py-2 rounded transition duration-200"
            >
                Create an Account
            </a>
        </form>

    </div>
</div>
 
<?php include('partials-front/footer.php'); ?>
