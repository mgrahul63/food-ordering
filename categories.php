<?php
    include('partials-front/menu.php');
    require_once('config/constants.php');
require_once './partials-front/functions.php';
    // Get selected category or default
    $category = isset($_GET['category']) ? $_GET['category'] : 'Breakfast';

    // Connect to database using PDO
    $objDb = new DbConnect();
    $conn = $objDb->connect();

    // Fetch foods from selected category
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if ($search !== '') {
        $sql = "SELECT * FROM tbl_food 
                WHERE category_name = :category  
                AND active = 'Yes' 
                AND title LIKE :search";
        $stmt = $conn->prepare($sql);
        $likeSearch = "%" . $search . "%";
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':search', $likeSearch);
    } else {
        $sql = "SELECT * FROM tbl_food 
                WHERE category_name = :category  
                AND active = 'Yes'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':category', $category);
    }
    $stmt->execute();
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- TailwindCSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">


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

<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">

        <h2 class="text-3xl font-bold text-center text-gray-800 mb-4">
            Explore Foods 
            <span class="font-semibold text-indigo-600"><?php echo htmlspecialchars($category); ?></span>
        </h2>
        <p class="text-center text-gray-600 mb-6">Find your favorite dishes from our menu.</p>
        <!-- Category Buttons -->
        <div class="flex justify-center space-x-4 mb-8">
            <a href="?category=Breakfast">
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Breakfast</button>
            </a>
            <a href="?category=Lunch">
                <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">Lunch</button>
            </a>
            <a href="?category=Dinner">
                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">Dinner</button>
            </a>
        </div>
 
         <!-- Search Form -->
        <form method="GET" class="flex justify-center mb-8">
            <input 
                type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>" 
            />
            <input 
                type="text" name="search" 
                placeholder="Search foods by title..." 
                class="border border-gray-300 px-4 py-2 rounded-l w-64"
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
            />
            <button 
                type="submit" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r"
            >
                Search
            </button>
        </form>

        <!-- Food List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($foods): ?>
                <?php foreach ($foods as $food): ?>
                    <div class="bg-white shadow-md rounded-lg p-4">
                        <?php if ($food['image_path']): ?>
                            <img 
                                src="images/food/<?php echo $food['image_path']; ?>" alt="Food Image" class="w-full h-40 object-cover rounded mb-3"
                                 onerror="this.onerror=null; this.src='images/food/default.png';"
                                >
                        <?php else: ?>
                            <div class="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-500 rounded mb-3">
                                No image available
                            </div>
                        <?php endif; ?>

                        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo htmlspecialchars($food['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($food['description']); ?></p>
                        <p>
                             <?php
                                 $available = getAvailableFood( $conn, $food['id'], $food['totalFood']);
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



                        <p class="text-gray-800 font-bold">Price: ৳<?php echo number_format($food['price'], 2); ?></p>
                        <!-- Button wrapper with flex -->
                        <div class="flex justify-between gap-2">
                            <a href="food-details.php?id=<?php echo urlencode($food['id']); ?>" class="flex-1 text-center bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                                Visit
                            </a>
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
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-red-500 col-span-3">No food found in <?php echo htmlspecialchars($category); ?> category.</p>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include('partials-front/footer.php'); ?>
