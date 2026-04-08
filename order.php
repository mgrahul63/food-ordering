<?php
session_start();

require_once('config/constants.php');
require_once './partials-front/functions.php';


$objDb = new DbConnect();
$conn = $objDb->connect();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Initialize session arrays
if (!isset($_SESSION['selected_foods'])) $_SESSION['selected_foods'] = [];
if (!isset($_SESSION['quantities'])) $_SESSION['quantities'] = [];

// Add food ID via GET
if (isset($_GET['id'])) {
    $foodId = intval($_GET['id']);
    if (!in_array($foodId, $_SESSION['selected_foods'])) {
        $_SESSION['selected_foods'][] = $foodId;
        $_SESSION['quantities'][$foodId] = 1;
    }
}

// Handle AJAX POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['food_id'])) {
    header('Content-Type: application/json');
    $foodId = intval($_POST['food_id']);

    if ($_POST['action'] === 'update_qty') {
        $qty = max(1, intval($_POST['quantity']));
        $_SESSION['quantities'][$foodId] = $qty;
        if (!in_array($foodId, $_SESSION['selected_foods'])) {
            $_SESSION['selected_foods'][] = $foodId;
        }
        echo json_encode(['status' => 'success']);
        exit();
    } elseif ($_POST['action'] === 'remove') {
        $_SESSION['selected_foods'] = array_filter($_SESSION['selected_foods'], function($id) use ($foodId) {
            return $id != $foodId;
        });
        unset($_SESSION['quantities'][$foodId]);
        echo json_encode(['status' => 'success']);
        exit();
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit();
}


 

include('partials-front/menu.php');
?>


<div class=" min-h-screen p-6">

    <!-- new orders -->
    <div class="max-w-7xl mx-auto bg-white rounded shadow p-6">
        <h2 class="text-3xl font-bold mb-6 text-center">Your Order</h2>

       <?php if (!empty($_SESSION['selected_foods'])): ?>
        <?php
        $total = 0;

        // Clean and ensure only unique integer IDs
        $foodIds = array_filter($_SESSION['selected_foods'], function($id) {
            return is_numeric($id);
        });
        $foodIds = array_unique($foodIds);

        if (!empty($foodIds)) {
            // ✅ Build placeholders
            $placeholders = implode(',', array_fill(0, count($foodIds), '?'));

            // ✅ Prepare statement
            $stmt = $conn->prepare("SELECT * FROM tbl_food WHERE id IN ($placeholders)");

            // ✅ Execute with food ID values
            $stmt->execute(array_values($foodIds)); // Ensure it's a zero-indexed array

            $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $foods = [];
        }
        ?>
        <div class="flex">
            <div id="order-list" class="space-y-4 w-6/12">
                <?php foreach ($foods as $food): 
                    $id = $food['id'];
                    $qty = $_SESSION['quantities'][$id] ?? 1;
                    $subtotal = $qty * $food['price'];
                    $total += $subtotal;
                ?>
                <div data-food-id="<?= $id ?>" class="flex items-center justify-between border p-4 rounded shadow">
                    <div class="flex items-center space-x-4">
                        <?php if (!empty($food['image_path'])): ?>
                            <img 
                            src="images/food/<?= htmlspecialchars($food['image_path']) ?>" 
                            alt="<?= htmlspecialchars($food['title']) ?>" 
                            class="w-24 h-24 rounded object-cover"
                             onerror="this.onerror=null; this.src='images/food/default.png';"
                            >
                        <?php else: ?>
                            <div class="w-24 h-24 bg-gray-200 flex items-center justify-center text-gray-500 rounded">No Image</div>
                        <?php endif; ?>
                        <div>
                            <h3 class="font-semibold text-lg"><?= htmlspecialchars($food['title']) ?></h3>
                            <p class="text-gray-600">৳<?= number_format($food['price'], 2) ?></p>
                        </div>
                    </div>  

                    <div class="flex items-center space-x-3" 

                        data-food-id="<?= $food['id'] ?>" 
                        data-available="<?= getAvailableFood($conn, $food['id'], $food['totalFood']); ?>"
                        data-name="<?= htmlspecialchars($food['title']) ?>">

                        <button class="qty-btn px-3 py-1 bg-gray-300 rounded" data-action="decrease">-</button>

                        <input type="number" min="1" value="<?= $qty ?>" class="qty-input w-16 border rounded text-center" />

                        <button class="qty-btn px-3 py-1 bg-gray-300 rounded" data-action="increase">+</button>
                    </div>
                    <div class="font-bold text-lg">৳<span class="subtotal"><?= number_format($subtotal, 2) ?></span></div>

                    <button class="remove-btn text-red-500 hover:underline">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>

           <div class="w-full md:w-6/12 px-4">
                <?php 
                    if (isset($_SESSION['user_id'])) {
                        $userId = $_SESSION['user_id'];

                        // Fetch user info from DB
                        $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    if (!empty($user)): 
                ?>
               <div class="bg-white rounded-xl shadow p-6 mb-6 border border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2 border-b pb-2">Customer Information</h2>
                    
                    <div class="text-green-700 font-medium mb-3">
                        ✅ Your food order has been successfully sent to this email.
                    </div>

                    <div class="text-gray-600 space-y-1">
                        <p><span class="font-medium">Name:</span> <?= htmlspecialchars($user['name']) ?></p>
                        <p><span class="font-medium">Email:</span> <?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>

                <?php endif; ?>

                <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Order Summary</h2>
                        <span class="text-2xl font-bold text-green-600">
                            ৳<span id="total"><?= number_format($total, 2) ?></span>
                        </span>
                    </div>

                    <form 
                        action="submit_order.php" 
                        method="post" 
                        class="text-right"
                        onsubmit="return confirm('Are you sure you want to place this order?');"
                     >
                        <button 
                            type="submit"
                            class="bg-green-600 text-white font-medium px-6 py-3 rounded-md hover:bg-green-700 transition duration-200"
                            
                        >
                            Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
             
        <?php else: ?>
            <p class="text-center text-gray-500">No foods selected yet. Please select some food first.</p>
        <?php endif; ?>
    </div>

    <br>

    <!-- previous orders list  -->
    <div class="max-w-7xl mx-auto bg-white rounded shadow p-2">
       <?php include('previous-orders-list.php'); ?>
    </div>

</div>
<script>
document.querySelectorAll('.qty-btn').forEach(button => {
    button.addEventListener('click', e => {
        const container = e.target.closest('[data-food-id]');
        const input = container.querySelector('.qty-input');
        const available = parseInt(container.getAttribute('data-available'));
        const foodName = container.getAttribute('data-name')|| 'This item';

        let qty = parseInt(input.value);

        if (e.target.dataset.action === 'increase') {
            if (qty + 1 > available) {
                alert(`${foodName} has only ${available} left in stock`);
                return;
            }
            qty++;
        } 
        else if (e.target.dataset.action === 'decrease' && qty > 1) {
            qty--;
        }

        input.value = qty;
        updateQuantity(container.getAttribute('data-food-id'), qty, container);
    });
});

document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', e => {
        let qty = parseInt(e.target.value);
        const container = e.target.closest('[data-food-id]');
        const available = parseInt(container.getAttribute('data-available'));
        const foodName = container.getAttribute('data-name') || 'This item';

        if (isNaN(qty) || qty < 1) {
            qty = 1;
        }

        if (qty > available) {
            alert(`${foodName} has only ${available} left`);
            qty = available;
        }

        e.target.value = qty;
        updateQuantity(container.getAttribute('data-food-id'), qty, container);
    });
});

document.querySelectorAll('.remove-btn').forEach(button => {
    button.addEventListener('click', e => {
        const container = e.target.closest('[data-food-id]');
        const foodName = container.getAttribute('data-name') || 'this item';

        if (!confirm(`Remove ${foodName}?`)) return;

        const foodId = container.getAttribute('data-food-id');

        fetch('order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'remove',
                food_id: foodId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                container.remove();
                recalcTotal();

                if (document.querySelectorAll('[data-food-id]').length === 0) {
                    document.getElementById('order-list').innerHTML = 
                        '<p class="text-center text-gray-500">No foods selected yet. Please select some food first.</p>';
                    document.getElementById('total').textContent = '0.00';
                }
            } else {
                alert(`Failed to remove ${foodName}`);
            }
        });
    });
});

function updateQuantity(foodId, qty, container) {
    fetch('order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'update_qty',
            food_id: foodId,
            quantity: qty
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const price = parseFloat(
                container.querySelector('p.text-gray-600').textContent.replace('৳', '')
            );

            const subtotalElem = container.querySelector('.subtotal');
            const newSubtotal = (price * qty).toFixed(2);

            subtotalElem.textContent = newSubtotal;
            recalcTotal();
        } else {
            alert('Failed to update quantity');
        }
    });
}

function recalcTotal() {
    let total = 0;

    document.querySelectorAll('[data-food-id]').forEach(container => {
        const subtotal = parseFloat(container.querySelector('.subtotal').textContent);
        total += subtotal;
    });

    document.getElementById('total').textContent = total.toFixed(2);
}
</script>



<?php include('partials-front/footer.php'); ?>
