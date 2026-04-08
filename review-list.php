<?php 
// Query orders for this food, newest first
$stmt = $conn->prepare("
    SELECT * 
    FROM ratings 
    WHERE food_id = :food_id
    ORDER BY updated_at DESC, created_at DESC
");
$stmt->execute([':food_id' => $food_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>

<?php if (!empty($orders)) : ?>
    <ul class="previous-orders-list">
        <?php foreach ($orders as $order) : ?>
            <?php if (!empty($order['review'])): 
                // Get user info
                $stm = $conn->prepare("SELECT * FROM users WHERE id = :id"); 
                $stm->execute([':id' => $order['user_id']]); 
                $user = $stm->fetch(PDO::FETCH_ASSOC); // fetch single row
            ?>
                
                <li class="order-item py-4">
                    <div class="flex items-start space-x-6">
                        <!-- Left side: Avatar + Name -->
                        <div class="flex flex-col items-center w-20">
                            <img 
                                src="<?= htmlspecialchars($user['profile_image'] ?? './images/default-avatar.png') ?>" 
                                alt="Profile" 
                                class="w-10 h-10 rounded-full object-cover"
                            >
                            <p class="text-sm font-semibold text-gray-800 mt-2 text-center">
                                <?= htmlspecialchars($user['name']) ?>
                            </p>
                        </div>

                        <!-- Right side: Review -->
                        <div class="flex-1 bg-gray-50 p-4 rounded-lg shadow-sm">
                            <p class="text-gray-700 text-sm"><?= htmlspecialchars($order['review']) ?></p>
                        </div>
                    </div>
                </li>
                
                <hr>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No previous orders found.</p>
<?php endif; ?>
