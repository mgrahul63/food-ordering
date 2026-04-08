<?php
include('partials-front/menu.php');
require_once('config/constants.php');
require_once './partials-front/functions.php';

// Connect to database using PDO
$objDb = new DbConnect();
$conn = $objDb->connect();

// Check if ID is set
if (isset($_GET['id'])) {
    $food_id = intval($_GET['id']);

    $sql = "SELECT * FROM tbl_food WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $food_id, PDO::PARAM_INT);
    $stmt->execute();

    $food = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        echo "<p class='text-center text-red-500'>Food not found.</p>";
        exit;
    }
} else {
    echo "<p class='text-center text-red-500'>No food ID provided.</p>";
    exit;
}
?>


<script>
    function addToCart(foodId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'food_id=' + encodeURIComponent(foodId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Added to cart!');
            } else if (data.message === 'Not logged in') { 
                window.location.href = 'login.php';
            } else {
                alert('Something went wrong!');
            }
        });
    }
</script>


<section class="py-10 bg-gray-100">

    <div class="container mx-auto px-4 max-w-3xl bg-white rounded-lg shadow-md p-6 mb-4">
        <img src="images/food/<?php echo $food['image_path'] ?: 'default.png'; ?>" 
             alt="<?php echo htmlspecialchars($food['title']); ?>" 
             class="w-full h-64 object-cover rounded mb-4"
             onerror="this.onerror=null;this.src='images/food/default.png';"
        >
        <h2 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($food['title']); ?></h2>
        <p class="text-gray-600 "><?php echo htmlspecialchars($food['description']); ?></p>

        <?php
                            // Get total rating sum for this food
                            $stmt = $conn->prepare("SELECT SUM(rating) as total_rating, COUNT(*) as total_votes FROM ratings WHERE food_id = :food_id");
                            $stmt->execute([':food_id' => $food['id']]);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);

                            $total_rating = $result['total_rating'] ?? 0;  // sum of all ratings
                            $total_votes  = $result['total_votes'] ?? 0;   // total number of ratings
                            $ratingAvg = $total_votes > 0 ? round($total_rating / $total_votes, 2) : 0;

                            $displayStar = 0;

                            if ($total_votes == 0) {
                                $displayStar = 0;
                            } 
                            else if ($ratingAvg <= 1.00) {
                                $displayStar = 1;
                            } elseif ($ratingAvg <= 1.50) {
                                $displayStar = 1.5;
                            } elseif ($ratingAvg <= 2.00) {
                                $displayStar = 2;
                            } elseif ($ratingAvg <= 2.50) {
                                $displayStar = 2.5;
                            } elseif ($ratingAvg <= 3.00) {
                                $displayStar = 3;
                            } elseif ($ratingAvg <= 3.50) {
                                $displayStar = 3.5;
                            } elseif ($ratingAvg <= 4.00) {
                                $displayStar = 4;
                            } elseif ($ratingAvg <= 4.50) {
                                $displayStar = 4.5;
                            } else {
                                $displayStar = 5;
                            }
                        ?>

                        <p>
                            Average Rating:

                            <span class="text-yellow-500">
                                <?php
                                // full stars
                                $full = floor($displayStar);

                                // half star
                                $half = ($displayStar - $full >= 0.5) ? 1 : 0;

                                // empty stars
                                $empty = 5 - $full - $half;

                                // print full stars
                                for ($i = 0; $i < $full; $i++) {
                                    echo "&#9733; "; // full ★
                                }

                                // print half star
                                if ($half) {
                                    echo "<span style='position:relative; display:inline-block; width:1em;'>
                                            <span style='color:gold;'>&#9733;</span>
                                            <span style='color:#ccc; position:absolute; left:0; width:50%; overflow:hidden;'>&#9733;</span>
                                        </span> ";
                                }

                                // print empty stars
                                for ($i = 0; $i < $empty; $i++) {
                                    echo "&#9734; "; // empty ☆
                                }
                                ?>
                            </span>

                            <?= $displayStar ?>  
                             <span style="color: #9ca3af;">(<?= $total_votes ?>)</span>
                        </p>

         <p>
            <?php
                $available = getAvailableFood($conn, $food['id'], $food['totalFood']);
            ?>
            <span class="text-view">
                <?php
                    if ($available > 0) {
                        echo "<b>Stock: </b>" . $available;
                    } else {
                        echo "<b>Stock: </b>Stock out";
                    }
                ?>
            </span>
        </p>


        <p class="text-blue-500 font-bold text-xl mb-6">৳ <?php echo $food['price']; ?></p>
        <?php if ($available > 0): ?>
            <button 
                onclick="addToCart(<?= $food['id'] ?>)" 
                class="order-btn bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition duration-200"
            >
                Order Now
            </button>
        <?php else: ?>
            <button 
                disabled
                class="bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed"
            >
                Out of Stock
            </button>
        <?php endif; ?>
    </div>
    <div class="container mx-auto px-4 max-w-3xl bg-white rounded-lg shadow-md p-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Customer Reviews</h3>
         <?php include('review-list.php'); ?>
    </div>
</section>

<?php include('partials-front/footer.php'); ?>
