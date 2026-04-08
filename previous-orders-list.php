<?php
require_once('config/constants.php');
$objDb = new DbConnect();
$conn = $objDb->connect();
 

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<p class='text-red-600 text-center mt-6'>Please log in to see your orders.</p>";
    exit;
}

 
try {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (empty($orders)) : ?>
    <h2 class="text-3xl font-bold text-center mb-8">Your Previous Orders</h2>
    <p class="text-gray-600 text-center mt-6">No previous orders found.</p>
<?php else : ?>
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-3xl font-bold text-center mb-8">Your Previous Orders</h2>

        <?php foreach ($orders as $order) : ?>
            <?php
            $food_ids = json_decode($order['food_ids'], true);
            $quantities = json_decode($order['quantities'], true);
            if (empty($food_ids)) continue;

            $placeholders = rtrim(str_repeat('?,', count($food_ids)), ',');
            $food_stmt = $conn->prepare("SELECT * FROM tbl_food WHERE id IN ($placeholders)");
            $food_stmt->execute($food_ids);
            $foods = $food_stmt->fetchAll(PDO::FETCH_ASSOC);

            $food_map = [];
            foreach ($foods as $food) {
                $food_map[$food['id']] = [
                    'title' => $food['title'],
                    'price' => $food['price'],
                    'image_path' => $food['image_path']
                ];
            }

            $total = 0;
            ?>

            <div class="bg-white border border-gray-300 rounded-lg shadow mb-8 p-4">
                <div 
                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center bg-gradient-to-r from-indigo-50 to-purple-50 border border-purple-100  p-2 shadow-sm gap-2 sm:gap-0">
                    <h2 class="text-2xl sm:text-xl text-gray-800 font-semibold">
                        <span class="text-gray-500 font-normal">Order Date:</span>
                        <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?>
                    </h2>
                    <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full 
                        <?php 
                            $status = strtolower($order['status']); 
                            echo match($status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-300 text-red-800',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </span>
                </div>



                <div class="overflow-x-auto">
                    <table class="min-w-full">
                      <thead class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white text-sm uppercase tracking-wider shadow">
                            <tr>
                                <th class="py-2 px-4 text-left">Image</th>
                                <th class="py-2 px-4 text-left">Food</th>
                                <th class="py-2 px-4 text-left">Quantity</th>
                                <th class="py-2 px-4 text-left">Price</th>
                                <th class="py-2 px-4 text-left">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($food_ids as $i => $food_id) :
                            $food = $food_map[$food_id] ?? ['title' => 'Unknown', 'price' => 0, 'image_path' => ''];
                            $qty = $quantities[$i] ?? 1;
                            $price = $food['price'];
                            $subtotal = $qty * $price;
                            $total += $subtotal;
                        ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="py-2 px-4">
                                    <img 
                                    src="images/food/<?= htmlspecialchars($food['image_path']) ?>" 
                                    alt="" 
                                    class="w-14 h-14 object-cover rounded"
                                    onerror="this.onerror=null; this.src='images/food/default.png';">
                                </td>
                                <td class="py-2 px-4"><?= htmlspecialchars($food['title']) ?></td>
                                <td class="py-2 px-4"><?= $qty ?></td>
                                <td class="py-2 px-4">৳<?= number_format($price, 2) ?></td>
                                <td class="py-2 px-4">৳<?= number_format($subtotal, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <br>
                </div>

                <div class="flex justify-between items-center bg-gradient-to-r from-gray-100 to-gray-200 px-4 py-2 rounded shadow-sm border">
                    <div class="text-lg font-semibold text-gray-800">
                        Total: <span class="text-green-600">৳<?= number_format($total, 2) ?></span>
                    </div>

                   <?php  
                    // Get existing rating for this order
                    $stmt = $conn->prepare("SELECT * FROM ratings WHERE order_id = :order_id AND user_id = :user_id ");
                    $stmt->execute([':order_id' => $order['id'], ':user_id' => $user_id]);
                    $rating = $stmt->fetch(PDO::FETCH_ASSOC);  

                    // Only show rating UI if order is completed
                    if (strtolower(trim($order['status'])) === 'completed'): 
                    ?>

                        <!-- review logic start -->
                        <div>
                            <?php 
                            if (!empty($rating['review'])): ?>
                                <p class="text-gray-700 mb-2">
                                    <strong>Your Review:</strong> <?= htmlspecialchars($rating['review']) ?>
                                </p>
                            <?php else: ?>
                                <input
                                    type="text"
                                    id="review-text-<?= $order['id'] ?>"
                                    placeholder="Write your review here..."
                                    class="border border-gray-300 rounded px-3 py-2 w-64"
                                    value=""
                                >
                                <input type="hidden"
                                    id="ratingID"
                                    value="<?= $rating['id'] ?>"
                                    >
                                <button
                                    type="button"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded shadow text-sm"
                                    onclick="submitReview(<?= $order['id'] ?>)">
                                    Submit Review
                                </button>
                            <?php endif; ?>
                        </div>
                        <!-- review logic end -->

                        <!-- rating stars logic -->
                        <?php if ($rating && $rating['rating'] >= 1 && $rating['rating'] <= 5): ?>
                            
                            <?php $existing_rating = $rating['rating']; ?>

                            <div class="text-yellow-500 font-semibold">
                                You rated this order:
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= $i <= $existing_rating ? "&#9733;" : "&#9734;"; ?>
                                <?php endfor; ?>
                            </div>

                        <?php else: ?>

                            <!-- User has NOT rated yet → Show clickable stars -->
                            <div class="rating" data-order-id="<?= $order['id'] ?>">
                                <span class="star" data-value="1">&#9734;</span>
                                <span class="star" data-value="2">&#9734;</span>
                                <span class="star" data-value="3">&#9734;</span>
                                <span class="star" data-value="4">&#9734;</span>
                                <span class="star" data-value="5">&#9734;</span>
                            </div>

                            <input type="hidden" id="rating-value-<?= $order['id'] ?>" 
                                name="rating[<?= $order['id'] ?>]" value="">

                             <input type="hidden"
                                    id="ratingID"
                                    value="<?= $rating['id']?>"
                                    >

                        <?php endif; ?>
                        <!-- rating stars logic end -->

                    <?php endif; ?>


                    <?php 
                     ?>

                   <div>
                        <?php if (strtolower($order['status']) === 'pending'){  ?>
                            <a href="cancel_order.php?id=<?= urlencode($order['id']) ?>" 
                            onclick="return confirm('Are you sure you want to cancel this order?');" 
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow text-sm transition">
                                Cancel Order
                            </a>
                        <?php } ?>
                    </div> 
                </div>

            </div>
        <?php endforeach; ?>

        <div id="success-message" class="text-green-600 font-medium mt-4 text-center hidden">Order cancelled successfully.</div>
    </div>
<?php endif; ?>

<?php
} catch (PDOException $e) {
    echo "<p class='text-red-500'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>


<style>
     .star {
    font-size: 30px;
    cursor: pointer;
    color: #ccc; /* gray empty */
    }

    .star.filled {
        color: gold; /* filled star */
    }
  </style>


<script> 
    document.querySelectorAll('.rating').forEach(ratingContainer => {
        const orderId = ratingContainer.dataset.orderId;
        const stars = ratingContainer.querySelectorAll('.star');
        const hiddenInput = document.getElementById('rating-value-' + orderId);
         const ratingId = document.getElementById('ratingID').value;

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = parseInt(star.getAttribute('data-value'));
                hiddenInput.value = rating;

                // Fill stars up to selected
                stars.forEach(s => {
                    s.classList.toggle('filled', parseInt(s.getAttribute('data-value')) <= rating);
                });

                // Send rating automatically to backend
                fetch('submit_rating.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId,  rating_id: ratingId, rating })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        console.log(`Rating ${rating} for order ${orderId} submitted successfully`);
                        location.reload();   // refresh page
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(err => console.error(err));
            });

            // Optional: hover preview
            star.addEventListener('mouseover', () => {
                const value = parseInt(star.getAttribute('data-value'));
                stars.forEach(s => s.classList.toggle('filled', parseInt(s.getAttribute('data-value')) <= value));
            });

            star.addEventListener('mouseout', () => {
                const value = parseInt(hiddenInput.value) || 0;
                stars.forEach(s => s.classList.toggle('filled', parseInt(s.getAttribute('data-value')) <= value));
            });
        });
    });
</script>

<script>
function submitReview(orderId) {
    const reviewInput = document.getElementById('review-text-' + orderId);
     const ratingId = document.getElementById('ratingID').value; 

    if (!reviewInput) {
        alert('Review input not found');
        return;
    }

    const review = reviewInput.value.trim();

    if (!review) {
        alert('Please write a review');
        return;
    }

    fetch('submit_rating.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            order_id: orderId,
            rating_id: ratingId,
            review: review
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Thanks for your feedback!');
             location.reload();   // refresh page
        } else {
            alert(data.message);
        }
    })
    .catch(() => alert('Network error'));
}
</script>
