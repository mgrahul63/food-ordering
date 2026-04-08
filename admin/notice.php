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

// ================= Handle Notice Add =================
if(isset($_POST['add_notice'])){
    $title = $_POST['title'];

    if(isset($_FILES['file']) && $_FILES['file']['error'] == 0){
        $fileTmp = $_FILES['file']['tmp_name'];
        $fileName = basename($_FILES['file']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedImage = ['jpg','jpeg','png','gif'];
        $allowedPdf = ['pdf'];

        if(in_array($fileExt, $allowedImage)){
            $file_type = 'image';
        } elseif(in_array($fileExt, $allowedPdf)){
            $file_type = 'pdf';
        } else {
            echo "<div class='alert alert-danger'>Invalid file type!</div>";
            exit;
        }

        $targetDir = "../uploads/notices/";
        if(!is_dir($targetDir)){
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir.$fileName;
        if(move_uploaded_file($fileTmp, $targetFile)){
            // ✅ Use the correct table name
            $stmt = $pdo->prepare("INSERT INTO notices (title, file_name, file_type, uploaded_at) VALUES (:title, :file_name, :file_type, NOW())");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':file_name', $fileName);
            $stmt->bindParam(':file_type', $file_type);

            if($stmt->execute()){
                echo "<div class='alert alert-success'>Notice added successfully.</div>";
                echo "<meta http-equiv='refresh' content='1'>";
            } else {
                echo "<div class='alert alert-danger'>Failed to add notice.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Failed to upload file.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Please select a file to upload.</div>";
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Manage Notices</h1>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="noticeTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">Notice List</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab">Add Notice</button>
      </li>
    </ul>

    <div class="tab-content" id="noticeTabContent">
        <!-- Notice List -->
        <div class="tab-pane fade show active" id="list" role="tabpanel">
            <?php
            $stmt = $pdo->prepare("SELECT * FROM notices ORDER BY uploaded_at DESC"); // ✅ corrected table name
            $stmt->execute();
            $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($notices) > 0): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>File Type</th> 
                        <th>Uploaded At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($notices as $notice): ?>
                    <tr>
                        <td><?php echo $notice['id']; ?></td>
                        <td><?php echo htmlspecialchars($notice['title']); ?></td>
                        <td>
                            <?php
                                $ext = strtolower($notice['file_type']);
                                echo $ext == 'pdf' ? '📄 PDF' : '🖼️ Image';
                            ?>
                        </td>  
                        <td class="px-4 py-3 text-gray-500"><?= date('d M Y', strtotime($notice['uploaded_at'])) ?></td>
                        <td class="px-4 py-3">
                            <button 
                                onclick="window.open('../uploads/notices/<?= htmlspecialchars($notice['file_name']) ?>','_blank')" 
                                class="btn btn-sm btn-primary">
                                View & Download
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-info">No notices found.</div>
            <?php endif; ?>
        </div>

        <!-- Add Notice -->
        <div class="tab-pane fade" id="add" role="tabpanel">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter notice title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">File (Image or PDF)</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="d-grid">
                    <input type="submit" name="add_notice" class="btn btn-primary" value="Add Notice">
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('partials/footer.php'); ?>
