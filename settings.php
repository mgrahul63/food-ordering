<?php
    session_start();
    require_once('config/constants.php');  // Make sure this defines DbConnect and DB constants

    // 1. Connect to database
    $objDb = new DbConnect();
    $conn  = $objDb->connect(); // returns a PDO instance

    // 2. Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
    $user_id = $_SESSION['user_id'];

    // 3. Handle AJAX update request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        // Collect inputs
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $contact  = trim($_POST['contact'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Basic validation
        if ($name === '' || $email === '') {
            echo json_encode(['status' => 'error', 'message' => 'Name and email are required.']);
            exit;
        }

        // Handle image upload if provided
        $imageName = null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $dest      = __DIR__ . '/images/users/' . $imageName;
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
        }

        // Build UPDATE statement
        $sql = "UPDATE users 
                SET name = :name, email = :email, contact = :contact, password = :password"
            . ($imageName ? ", image = :image" : "")
            . " WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $params = [
            ':name'     => $name,
            ':email'    => $email,
            ':contact'  => $contact,
            ':password' => $password,
            ':id'       => $user_id
        ];
        if ($imageName) {
            $params[':image'] = $imageName;
        }

        $success = $stmt->execute($params);

        echo json_encode([
            'status'  => $success ? 'success' : 'error',
            'message' => $success ? 'Profile updated successfully.' : 'Failed to update profile.'
        ]);
        exit;
    }

    // 4. Fetch current user data
    $stmt = $conn->prepare("SELECT name, email, contact, password, image FROM users WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$userInfo) {
        echo "<p class='text-red-500 p-4'>User not found.</p>";
        exit;
    }

    // Determine image src
    $imageSrc = !empty($userInfo['image'])
        ? "images/users/" . htmlspecialchars($userInfo['image'])
        : "images/users/default.png";


?>

<?php include('partials-front/menu.php'); ?>

    <div class="max-w-xl mx-auto mt-12 bg-white p-8 rounded-xl shadow-md">
        <h1 class="text-2xl font-bold mb-6">Account Settings</h1>
        <div id="message" class="hidden mb-4 p-3 rounded text-sm font-medium"></div>

        <form id="settingsForm" enctype="multipart/form-data" class="space-y-6">
            <!-- Profile Image -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Profile Image</label>
                <div class="flex items-center space-x-4">
                    <img
                    id="previewImage"
                    src="<?= $imageSrc ?>"
                    alt="Profile"
                    class="w-24 h-24 rounded-full object-cover border border-gray-300"
                    />
                    <input
                    type="file"
                    name="image"
                    id="imageInput"
                    accept="image/*"
                    class="text-sm text-gray-600 file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100"
                    />
                </div>
            </div>


            <!-- Name -->
            <div>
            <label class="block text-gray-700 font-semibold mb-1">Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($userInfo['name']) ?>"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                            focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Email -->
            <div>
            <label class="block text-gray-700 font-semibold mb-1">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($userInfo['email']) ?>"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                            focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Contact -->
            <div>
            <label class="block text-gray-700 font-semibold mb-1">Contact</label>
            <input type="text" name="contact" value="<?= htmlspecialchars($userInfo['contact']) ?>"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                            focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Password -->
            <div>
            <label class="block text-gray-700 font-semibold mb-1">Password</label>
            <input type="password" name="password" value="<?= htmlspecialchars($userInfo['password']) ?>"
                    class="w-full border border-gray-300 rounded-md px-4 py-2
                            focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded-md
                        hover:bg-blue-700 transition">Update Profile
            </button>
            
        </form>
    </div>

    <script>
        // Preview selected image immediately
        document.getElementById('imageInput').addEventListener('change', function(e) {
        const [file] = this.files;
        if (file) {
            document.getElementById('previewImage').src = URL.createObjectURL(file);
        }
        });

        // AJAX submission
        document.getElementById('settingsForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const msgDiv = document.getElementById('message');
        const form = e.target;
        const data = new FormData(form);
        data.append('ajax', '1');

        const res  = await fetch('', { method: 'POST', body: data });
        const json = await res.json();

        msgDiv.textContent = json.message;
        msgDiv.classList.remove('hidden', 'text-green-600', 'bg-green-100', 'text-red-600', 'bg-red-100');
        if (json.status === 'success') {
            msgDiv.classList.add('text-green-600', 'bg-green-100');
        } else {
            msgDiv.classList.add('text-red-600', 'bg-red-100');
        }
        });
    </script>

<?php include('partials-front/footer.php'); ?>
