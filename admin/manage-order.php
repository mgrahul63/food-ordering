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

// ================= Handle Update Order =================
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE orders SET status=:status WHERE id=:id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);

    if($stmt->execute()){
        echo "<div class='alert alert-success mt-3'>Order status updated successfully.</div>";
        echo "<meta http-equiv='refresh' content='1'>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to update order status.</div>";
    }
}
?>

<div class="container my-3">
    <h1 class="mb-4">Manage Orders</h1>

    <!-- Filter Buttons -->
    <div class="mb-3">
        <button class="btn btn-primary filter-btn" data-status="All">All</button>
        <button class="btn btn-warning filter-btn" data-status="Pending">Pending</button>
        <button class="btn btn-success filter-btn" data-status="Completed">Completed</button>
        <button class="btn btn-danger filter-btn" data-status="Cancelled">Cancelled</button>
    </div>

    <?php 
    $stmt = $pdo->prepare("SELECT * FROM orders ORDER BY id DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($orders) > 0): ?>
    <table class="table table-bordered table-hover" id="orderTable">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Food</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Customer Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $order): 
                $food_ids = json_decode($order['food_ids'], true);
                $quantities = json_decode($order['quantities'], true);
                $prices = json_decode($order['prices'], true);

                // Fetch food names
                $food_names = [];
                if($food_ids){
                    $in  = str_repeat('?,', count($food_ids) - 1) . '?';
                    $food_stmt = $pdo->prepare("SELECT id, title FROM tbl_food WHERE id IN ($in)");
                    $food_stmt->execute($food_ids);
                    $foods = $food_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // id => title
                    foreach($food_ids as $fid){
                        $food_names[] = $foods[$fid] ?? "Unknown";
                    }
                }
            ?>
            <tr class="order-row" data-status="<?php echo $order['status']; ?>">
                <form method="POST"> 
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo implode(", ", $food_names); ?></td>
                    <td><?php echo implode(", ", $prices); ?></td>
                    <td><?php echo implode(", ", $quantities); ?></td>
                    <td><?php echo $order['totalPrice']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td>
                        <span class="text-view"><?php echo $order['status']; ?></span>
                        <select name="status" class="form-edit form-control" style="display:none;">
                            <option value="Pending" <?php if($order['status']=='Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Completed" <?php if($order['status']=='Completed') echo 'selected'; ?>>Completed</option>
                            <option value="Cancelled" <?php if($order['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </td>
                    <td><?php echo $order['user_name']; ?></td>
                    <td><?php echo $order['user_contact']; ?></td>
                    <td><?php echo $order['user_email']; ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm edit-btn">Edit</button>
                        <button type="submit" name="update" class="btn btn-success btn-sm save-btn" style="display:none;">Save</button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-btn" style="display:none;">Cancel</button>
                    </td>
                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<script>
    // Inline Edit / Cancel
    document.querySelectorAll('.edit-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let row = btn.closest('tr');
            row.querySelector('.text-view').style.display='none';
            row.querySelector('.form-edit').style.display='block';
            btn.style.display='none';
            row.querySelector('.save-btn').style.display='inline-block';
            row.querySelector('.cancel-btn').style.display='inline-block';
        });
    });

    document.querySelectorAll('.cancel-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let row = btn.closest('tr');
            row.querySelector('.text-view').style.display='block';
            row.querySelector('.form-edit').style.display='none';
            row.querySelector('.edit-btn').style.display='inline-block';
            row.querySelector('.save-btn').style.display='none';
            btn.style.display='none';
        });
    });

    // Filter Buttons
    document.querySelectorAll('.filter-btn').forEach(btn=>{
        btn.addEventListener('click', function(){
            let status = btn.getAttribute('data-status');
            document.querySelectorAll('.order-row').forEach(row=>{
                if(status === 'All' || row.getAttribute('data-status') === status){
                    row.style.display='';
                } else {
                    row.style.display='none';
                }
            });
        });
    });
</script>

<?php include('partials/footer.php'); ?>
