<?php
require_once('config/constants.php');
$objDb = new DbConnect();
$conn = $objDb->connect();

// Fetch all notices ordered by latest
try {
    $stmt = $conn->prepare("SELECT * FROM notices ORDER BY uploaded_at DESC");
    $stmt->execute();
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p class='text-red-600 text-center mt-4'>Error: " . $e->getMessage() . "</p>";
    exit;
}
?>
 
 <?php include('partials-front/menu.php'); ?>
<div class="bg-gray-100 min-h-screen">

    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold text-center text-blue-700 mb-8">📌 Notice Board</h1>

        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full table-auto text-center text-sm">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">File Type</th>
                        <th class="px-4 py-3">Posted On</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (!empty($notices)): ?>
                        <?php foreach ($notices as $index => $notice): ?>
                            <tr class="hover:bg-gray-100">
                                <td class="px-4 py-3"><?= $index + 1 ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($notice['title']) ?></td>
                                <td class="px-4 py-3 text-gray-600">
                                    <?php
                                        $ext = strtolower($notice['file_type']);
                                        echo $ext == 'pdf' ? '📄 PDF' : '🖼️ Image';
                                    ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500"><?= date('d M Y', strtotime($notice['uploaded_at'])) ?></td>
                                <td class="px-4 py-3">
                                   <button 
                                        onclick="openAndDownload('<?= htmlspecialchars($notice['file_name']) ?>')" 
                                        class="inline-block bg-blue-500 hover:bg-blue-700 text-white text-sm font-semibold py-1 px-3 rounded">
                                        View & Download
                                    </button>
                                                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-4 py-5 text-gray-500">No notices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
 
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