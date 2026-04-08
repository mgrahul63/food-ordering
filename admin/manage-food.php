<?php 
session_start();
if(!isset($_SESSION['admin'])){
    header('location: login.php');
    exit; // Always exit after redirect
}
include('./partials/menu.php');   
include('../config/constants.php');  

require_once './partials/functions.php';

$objDb = new DbConnect();
$pdo = $objDb->connect(); // PDO connection

// ================= Handle Add Food =================
if(isset($_POST['submit'])){
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_name = $_POST['category_name'];
    $featured = $_POST['featured'];
    $active = $_POST['active'];

    // Handle Image Upload
    if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != ""){
        $image_name = time().'_'.basename($_FILES['image']['name']);
        $target = "../images/food/".$image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        $image_name = "default.png";
    }

    $stmt = $pdo->prepare("INSERT INTO tbl_food (title, description, price, image_path, category_name, featured, active) VALUES (:title, :description, :price, :image_path, :category_name, :featured, :active)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':image_path', $image_name);
    $stmt->bindParam(':category_name', $category_name);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':active', $active);

    if($stmt->execute()){
        echo "<div class='alert alert-success mt-3'>Food added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to add food.</div>";
    }
}

// ================= Handle Update Food =================
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_name = $_POST['category_name'];
    $featured = $_POST['featured'];
    $active = $_POST['active'];
    $newAddFood = $_POST['newAddFood'];
     

    // Handle Image Upload
    if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != ""){
        $image_name = time().'_'.basename($_FILES['image']['name']);
        $target = "../images/food/".$image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        // Keep old image
        $stmt_img = $pdo->prepare("SELECT image_path FROM tbl_food WHERE id=:id");
        $stmt_img->bindParam(':id', $id);
        $stmt_img->execute();
        $image_name = $stmt_img->fetch(PDO::FETCH_ASSOC)['image_path'];
    }

    $stmt = $pdo->prepare("
        UPDATE tbl_food
        SET 
            title = :title,
            description = :description,
            price = :price,
            image_path = :image_path,
            category_name = :category_name,
            featured = :featured,
            active = :active,
            newAddFood = :newAddFood,
            totalFood = totalFood + :newAddFood
        WHERE id = :id
    ");

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':image_path', $image_name);
    $stmt->bindParam(':category_name', $category_name);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':active', $active);
    $stmt->bindParam(':id', $id);

    // Bind the integer
    $newAddFood = intval($newAddFood); // ensure integer
    $stmt->bindParam(':newAddFood', $newAddFood, PDO::PARAM_INT);
    


    if($stmt->execute()){
        echo "<div class='alert alert-success mt-3'>Food updated successfully.</div>";
        echo "<meta http-equiv='refresh' content='1'>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to update food.</div>";
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Manage Food</h1>

    <!-- Navigation Tabs -->
    <div class="mb-4">
        <button class="btn btn-primary category-btn" data-target="Breakfast">Breakfast</button>
        <button class="btn btn-primary category-btn" data-target="Lunch">Lunch</button>
        <button class="btn btn-primary category-btn" data-target="Dinner">Dinner</button>
        <button class="btn btn-success category-btn" data-target="AddFood">Add Food</button>
    </div>

    <!-- ================= Food Lists ================= -->
    <?php 
    $categories = ['Breakfast','Lunch','Dinner'];
    foreach($categories as $cat): ?>
        <div class="food-list" id="<?php echo $cat; ?>" style="display: <?php echo ($cat=='Breakfast')?'block':'none'; ?>">
            <h3><?php echo $cat; ?> Foods</h3>
            <?php 
            $stmt = $pdo->prepare("SELECT * FROM tbl_food WHERE category_name=:cat ORDER BY id DESC");
            $stmt->bindParam(':cat', $cat);
            $stmt->execute();
            $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($foods)>0): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Image</th>
                        <th>Featured</th>
                        <th>Active</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($foods as $food): ?>
                    <tr id="row-<?php echo $food['id']; ?>">
                        <form method="POST" enctype="multipart/form-data">
                            <td><?php echo $food['id']; ?></td>
                            <td>
                                <span class="text-view"><?php echo htmlspecialchars($food['title']); ?></span>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($food['title']); ?>" class="form-control form-edit" style="display:none;">
                            </td>
                            <td>
                                <span class="text-view"><?php echo htmlspecialchars($food['description']); ?></span>
                                <textarea name="description" class="form-control form-edit" style="display:none;"><?php echo htmlspecialchars($food['description']); ?></textarea>
                            </td>
                            <td>
                                <span class="text-view">$<?php echo $food['price']; ?></span>
                                <input type="number" step="0.01" name="price" value="<?php echo $food['price']; ?>" class="form-control form-edit" style="display:none;">
                            </td>
                            <td>
                                <span class="text-view">
                                   <?php
                                        $imagePath = "../images/food/" . $food['image_path']; // prepend folder

                                        // Check if file exists and is not empty
                                        if(empty($food['image_path']) || !file_exists($imagePath)){
                                            $imagePath = "../images/food/default.png";
                                        }
                                        ?>

                                        <img src="<?php echo $imagePath; ?>" width="100">

                                </span>
                                <input type="file" name="image" class="form-control form-edit" style="display:none;">
                            </td>
                            <td>
                                <span class="text-view"><?php echo $food['featured']; ?></span>
                                <select name="featured" class="form-select form-edit" style="display:none;">
                                    <option value="Yes" <?php echo ($food['featured']=='Yes')?'selected':''; ?>>Yes</option>
                                    <option value="No" <?php echo ($food['featured']=='No')?'selected':''; ?>>No</option>
                                </select>
                            </td>
                            <td>
                                <span class="text-view"><?php echo $food['active']; ?></span>
                                <select name="active" class="form-select form-edit" style="display:none;">
                                    <option value="Yes" <?php echo ($food['active']=='Yes')?'selected':''; ?>>Yes</option>
                                    <option value="No" <?php echo ($food['active']=='No')?'selected':''; ?>>No</option>
                                </select>
                            </td>
                            <td>
                                    <?php
                                        $available = getAvailableFood($pdo, $food['id'], $food['totalFood']);
                                    ?>
                                    <span class="text-view"><?php echo $available; ?></span>
                                    <input type="number" step="0.01" name="newAddFood" value="<?php echo $available; ?>" class="form-control form-edit" style="display:none;">
                            </td>

                            <td>
                                <button type="button" class="btn btn-warning btn-sm edit-btn">Edit</button>
                                <button type="submit" name="update" class="btn btn-success btn-sm save-btn" style="display:none;">Save</button>
                                <button type="button" class="btn btn-secondary btn-sm cancel-btn" style="display:none;">Cancel</button>
                                <a href="delete-food.php?id=<?php echo $food['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                            <input type="hidden" name="id" value="<?php echo $food['id']; ?>">
                            <input type="hidden" name="category_name" value="<?php echo $cat; ?>">
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-info">No foods added yet.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <!-- ================= Add Food Form ================= -->
    <div id="AddFood" style="display:none;">
        <h3>Add New Food</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_name" class="form-select" required>
                    <option value="Breakfast">Breakfast</option>
                    <option value="Lunch">Lunch</option>
                    <option value="Dinner">Dinner</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Featured</label>
                <select name="featured" class="form-select">
                    <option value="Yes">Yes</option>
                    <option value="No" selected>No</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Active</label>
                <select name="active" class="form-select">
                    <option value="Yes" selected>Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
            <div class="d-grid">
                <input type="submit" name="submit" class="btn btn-success" value="Add Food">
            </div>
        </form>
    </div>
</div>

<script>
    // Category Tabs
    document.querySelectorAll('.category-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let target = btn.getAttribute('data-target');
            document.querySelectorAll('.food-list, #AddFood').forEach(div=>{
                div.style.display='none';
            });
            document.getElementById(target).style.display='block';
        });
    });

    // Inline Edit / Cancel
    document.querySelectorAll('.edit-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let row = btn.closest('tr');
            row.querySelectorAll('.text-view').forEach(el=>el.style.display='none');
            row.querySelectorAll('.form-edit').forEach(el=>el.style.display='block');
            btn.style.display='none';
            row.querySelector('.save-btn').style.display='inline-block';
            row.querySelector('.cancel-btn').style.display='inline-block';
        });
    });
    document.querySelectorAll('.cancel-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let row = btn.closest('tr');
            row.querySelectorAll('.text-view').forEach(el=>el.style.display='block');
            row.querySelectorAll('.form-edit').forEach(el=>el.style.display='none');
            row.querySelector('.edit-btn').style.display='inline-block';
            row.querySelector('.save-btn').style.display='none';
            btn.style.display='none';
        });
    });
</script>

<?php include('partials/footer.php'); ?>
