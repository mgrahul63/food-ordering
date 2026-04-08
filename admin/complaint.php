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

// ================= Handle Admin Response =================
if(isset($_POST['respond'])){
    $id = $_POST['id'];
    $response = $_POST['admin_response'];
    $responded_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("UPDATE complaints SET admin_response=:response, responded_at=:responded_at WHERE id=:id");
    $stmt->bindParam(':response', $response);
    $stmt->bindParam(':responded_at', $responded_at);
    $stmt->bindParam(':id', $id);

    if($stmt->execute()){
        echo "<div class='alert alert-success mt-3'>Response submitted successfully.</div>";
        echo "<meta http-equiv='refresh' content='1'>";
    } else {
        echo "<div class='alert alert-danger mt-3'>Failed to submit response.</div>";
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Manage Complaints</h1>

    <?php 
    $stmt = $pdo->prepare("SELECT * FROM complaints ORDER BY created_at DESC");
    $stmt->execute();
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($complaints) > 0): ?>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Admin Response</th>
                <th>Responded At</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($complaints as $complaint): ?>
            <tr>
                <form method="POST">
                    <td><?php echo $complaint['id']; ?></td>
                    <td><?php echo $complaint['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['message']); ?></td>
                    <td>
                        <span class="text-view"><?php echo htmlspecialchars($complaint['admin_response']); ?></span>
                        <textarea 
                            id="admin_response" 
                            name="admin_response" 
                            class="form-control form-edit" 
                            style="display:none;"><?php echo htmlspecialchars($complaint['admin_response']); ?></textarea>
                    </td>

                    <td><?php echo $complaint['responded_at']; ?></td>
                    <td><?php echo $complaint['created_at']; ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm edit-btn">Respond</button>
                        <button type="submit" name="respond" class="btn btn-success btn-sm save-btn" style="display:none;">Save</button>
                        <button type="button" class="btn btn-secondary btn-sm cancel-btn" style="display:none;">Cancel</button>
                    </td>
                    <input type="hidden" name="id" value="<?php echo $complaint['id']; ?>">
                </form>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-info">No complaints found.</div>
    <?php endif; ?>
</div>

<script>
// Inline Respond / Cancel
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
</script>

<?php include('partials/footer.php'); ?>
