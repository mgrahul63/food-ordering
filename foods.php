<?php
include('partials-front/menu.php');
require_once('config/constants.php');
require_once './partials-front/functions.php';


$objDb = new DbConnect();
$pdo = $objDb->connect(); // Now using PDO

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM tbl_food WHERE active = 'Yes'";
if ($search !== '') {
    $sql .= " AND title LIKE :search";
}

$stmt = $pdo->prepare($sql);

if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->execute();
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<div class="container mx-auto px-4">
    <section class="food-search text-center py-4 bg-gray-100">
        <div class="container mx-auto">
            <form action="" method="GET" class="flex justify-center items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search for food..." 
                    value="<?php echo htmlspecialchars($search); ?>" 
                    class="border border-gray-300 px-4 py-2 rounded w-1/2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <button 
                    type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Search
                </button>
            </form>
        </div>
    </section>

    <section class="food-menu py-10">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Food Menu</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (count($foods) > 0): ?>
                    <?php foreach ($foods as $row): ?>
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <img 
                                src="images/food/<?php echo $row['image_path'] ?: 'default.png'; ?>" 
                                alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                class="w-full h-48 object-cover"
                                onerror="this.onerror=null;this.src='images/food/default.png';"
                            >
                            <div class="p-4">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </h3>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($row['description']); ?></p>

                            <?php
                            // Get total rating sum for this food
                            $stmt = $conn->prepare("SELECT SUM(rating) as total_rating, COUNT(*) as total_votes FROM ratings WHERE food_id = :food_id");
                            $stmt->execute([':food_id' => $row['id']]);
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
                                        $available = getAvailableFood($pdo, $row['id'], $row['totalFood']);
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
                                <p class="text-blue-500 font-bold text-lg mb-3">৳ <?php echo $row['price']; ?></p>
                                <!-- Button wrapper with flex -->
                                <div class="flex justify-between gap-2">
                                    <a href="food-details.php?id=<?php echo urlencode($row['id']); ?>" class="flex-1 text-center bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                                        Visit
                                    </a>
                                    <?php if ($available > 0): ?>
                                        <button 
                                            onclick="addToCart(<?= $row['id'] ?>)" 
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
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 col-span-3">No food found.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    </div>
<?php include('partials-front/footer.php'); ?>