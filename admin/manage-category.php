<?php 
session_start();
if(!isset($_SESSION['admin'])){
    header('location: login.php');
    exit; // Always exit after redirect
}

include('./partials/menu.php');   
include('../config/constants.php');  

$objDb = new DbConnect();
$pdo = $objDb->connect(); // PDO connection

 
// ======================== Handle Add Category ========================
if(isset($_POST['submit'])){
    $title = $_POST['title'];
    $featured = $_POST['featured'];
    $active = $_POST['active'];

    // Handle Image Upload
    if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != ""){
        $image_name = time().'_'.basename($_FILES['image']['name']);
        $target = "../images/category/".$image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        $image_name = "";
    }

    $stmt = $pdo->prepare("INSERT INTO tbl_category (title, image_name, featured, active) VALUES (:title, :image_name, :featured, :active)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':image_name', $image_name);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':active', $active);

    if($stmt->execute()){
        echo "<div class='alert alert-success mt-3'>Category added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to add category.</div>";
    }
}

// ======================== Handle Update Category ========================
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $title = $_POST['title'];
    $featured = $_POST['featured'];
    $active = $_POST['active'];

    // Handle Image Upload
    if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != ""){
        $image_name = time().'_'.basename($_FILES['image']['name']);
        $target = "../images/category/".$image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        // Keep old image
        $stmt_img = $pdo->prepare("SELECT image_name FROM tbl_category WHERE id=:id");
        $stmt_img->bindParam(':id', $id);
        $stmt_img->execute();
        $image_name = $stmt_img->fetch(PDO::FETCH_ASSOC)['image_name'];
    }

    // Update DB
    $stmt = $pdo->prepare("UPDATE tbl_category SET title=:title, image_name=:image_name, featured=:featured, active=:active WHERE id=:id");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':image_name', $image_name);
    $stmt->bindParam(':featured', $featured);
    $stmt->bindParam(':active', $active);
    $stmt->bindParam(':id', $id);

    if($stmt->execute()){
        echo "<div class='alert alert-success mt-3'>Category updated successfully.</div>";
        echo "<meta http-equiv='refresh' content='1'>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to update category.</div>";
    }
}
?>

<div class="container my-3">
    <h1 class="mb-2">Manage Category</h1>

    <!-- Navigation Buttons -->
    <div class="mb-3">
        <button id="showList" class="btn btn-primary">Category List</button>
        <button id="showForm" class="btn btn-success">Add Category</button>
    </div>

    <!-- ================= Category List ================= -->
    <div id="categoryList">
        <?php
        $stmt = $pdo->query("SELECT * FROM tbl_category ORDER BY id DESC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($categories) > 0):
        ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Image</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($categories as $cat): ?>
                <tr id="row-<?php echo $cat['id']; ?>">
                    <form method="POST" enctype="multipart/form-data">
                    <td><?php echo $cat['id']; ?></td>
                    
                    <td>
                        <span class="text-view"><?php echo htmlspecialchars($cat['title']); ?></span>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($cat['title']); ?>" class="form-control form-edit" style="display:none;">
                    </td>
                    
                    <td>
                        <span class="text-view">
                            <?php if($cat['image_name']): ?>
                                <img src="../images/category/<?php echo $cat['image_name']; ?>" width="100">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </span>
                        <input type="file" name="image" class="form-control form-edit" style="display:none;">
                    </td>
                    
                    <td>
                        <span class="text-view"><?php echo $cat['featured']; ?></span>
                        <select name="featured" class="form-select form-edit" style="display:none;">
                            <option value="Yes" <?php echo ($cat['featured']=='Yes')?'selected':''; ?>>Yes</option>
                            <option value="No" <?php echo ($cat['featured']=='No')?'selected':''; ?>>No</option>
                        </select>
                    </td>
                    
                    <td>
                        <span class="text-view"><?php echo $cat['active']; ?></span>
                        <select name="active" class="form-select form-edit" style="display:none;">
                            <option value="Yes" <?php echo ($cat['active']=='Yes')?'selected':''; ?>>Yes</option>
                            <option value="No" <?php echo ($cat['active']=='No')?'selected':''; ?>>No</option>
                        </select>
                    </td>
                    
                    <td>
                        <button type="button" class="btn btn-warning btn-sm edit-btn">Edit</button>
                        <button type="submit" name="update" class="btn btn-success btn-sm save-btn" style="display:none;">Save</button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-btn" style="display:none;">Cancel</button>
                        <a href="delete-category.php?id=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-info">No categories added yet.</div>
        <?php endif; ?>
    </div>

    <!-- ================= Add Category Form ================= -->
    <div id="addCategoryForm" style="display:none;">
        <h3>Add New Category</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" class="form-control" id="title" required>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Category Image</label>
                <input type="file" name="image" class="form-control" id="image" accept="image/*">
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
                <input type="submit" name="submit" class="btn btn-success" value="Add Category">
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle Add/List
    document.getElementById('showList').addEventListener('click', function(){
        document.getElementById('categoryList').style.display='block';
        document.getElementById('addCategoryForm').style.display='none';
    });
    document.getElementById('showForm').addEventListener('click', function(){
        document.getElementById('categoryList').style.display='none';
        document.getElementById('addCategoryForm').style.display='block';
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
