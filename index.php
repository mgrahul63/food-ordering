<?php

include('partials-front/menu.php');
require_once('config/constants.php');
require_once './partials-front/functions.php';

$objDb = new DbConnect();
$conn = $objDb->connect();

// Helper function to fetch top 5 foods by category
function getFoodsByCategory($conn, $category) {
    $sql = "SELECT * FROM tbl_food WHERE category_name = :category_name AND active = 'Yes' LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category_name', $category); // MATCHES NOW
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
 
// Fetch all notices ordered by latest
try {
    $stmt = $conn->prepare("SELECT * FROM notices ORDER BY uploaded_at DESC LIMIT 1");
    $stmt->execute();
    $notice = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p class='text-red-600 text-center mt-4'>Error: " . $e->getMessage() . "</p>";
    exit;
}

$categories = ['Breakfast', 'Lunch', 'Dinner']; 
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

<section class="pt-3 pb-10 bg-gray-50">
    <div class="container mx-auto">
        <h2 class="text-4xl font-bold text-center text-gray-800 mb-3">Chakrobak Cafeteria Management System</h2>
        <p style="background: green; padding: 5px">
           <?php if(!empty($notice)): ?>
            <marquee behavior="scroll" direction="left">
                <a href="#" onclick="openAndDownload('<?= htmlspecialchars($notice['file_name']) ?>')" style="color: orange; font-size: 20px">
                    <?= htmlspecialchars($notice['title']) ?>
                </a>
            </marquee>
          <?php else: ?>
              <p>No notices available.</p>
          <?php endif; ?>
        </p>
       

        
       <!-- Cover Image -->
      <div class="mb-2">
        <img src="images/coverImage/cover.png" 
            alt="Cover" 
            class="w-full shadow-lg" 
            style="height: 500px; object-fit: cover; background-color: #f8f9fa;">
      </div>

      <div  style="text-align: center; font-size: 20px; color: green; margin-bottom: 5px">
        <p>আস্থায়,ভরসায়!! খাবারের গুনগতমান রক্ষায় আমরা  সপ্রতিজ্ঞায় বদ্ধ পরিকর।
          <br>
 <span style="color: orange">-কর্তৃপক্ষ(চক্রবাক ক্যাফেটেরিয়া)</span></p>
      </div>
       
        <!-- Loop through categories -->
        <?php foreach ($categories as $category): 
            $foods = getFoodsByCategory($conn, $category);
        ?>
            <div class="mb-12 px-4">
                <h2 class="text-3xl font-semibold text-gray-700 mb-6"><?php echo htmlspecialchars($category); ?> Items</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-8 p-4 bg-gray-50 rounded-lg">
                <?php if ($foods): ?>
                  <?php foreach ($foods as $food): ?>
                    <div class="bg-white border border-gray-200 rounded-lg shadow-md p-5 hover:shadow-xl transition-shadow duration-300 flex flex-col">
                      <a href="food-details.php?id=<?php echo $food['id']; ?>" class="block mb-4 overflow-hidden rounded-md">
                        <?php if ($food['image_path']): ?>
                          <img 
                            src="images/food/<?php echo $food['image_path']; ?>" 
                            alt="<?php echo htmlspecialchars($food['title']); ?>" 
                            class="w-full h-32 object-cover rounded-md"
                            onerror="this.onerror=null; this.src='images/food/default.png';"
                          >
                        <?php else: ?>
                          <div class="w-full h-32 bg-gray-200 flex items-center justify-center rounded-md">
                            <span class="text-gray-400 italic">No image</span>
                          </div>
                        <?php endif; ?>
                      </a>
                      <h4 class="text-lg font-semibold text-gray-900 mb-1 truncate" title="<?php echo htmlspecialchars($food['title']); ?>">
                        <?php echo htmlspecialchars($food['title']); ?>
                      </h4> 

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
                        
                      <p class="text-indigo-600 font-bold text-xl mb-2">৳<?php echo number_format($food['price'], 2); ?></p>
                      <a href="food-details.php?id=<?php echo $food['id']; ?>" class="mt-auto inline-block text-center bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 transition">
                        View Details
                      </a>
                    </div>
                  <?php endforeach; ?>

                  <div class="col-span-full text-center mt-6">
                    <a href="categories.php" class="inline-block bg-gray-800 text-white px-6 py-3 rounded-md hover:bg-gray-900 transition">
                      View More Categories
                    </a>
                  </div>
                  
                <?php else: ?>
                  <p class="text-red-500 col-span-full text-center py-8 text-lg font-medium">
                    No food found in <span class="font-semibold"><?php echo htmlspecialchars($category); ?></span> category.
                  </p>
                <?php endif; ?>
              </div>

            </div>
        <?php endforeach; ?>
    </div>
</section>


 
<script>
    function openAndDownload(filename) {
        const fileUrl = `./uploads/notices/${encodeURIComponent(filename)}`;

        // Open in new tab
        window.open(fileUrl, '_blank');

        // Trigger download
        const a = document.createElement('a');
        a.href = fileUrl;
        a.setAttribute('download', filename);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>


<?php include('partials-front/footer.php'); ?>
