<?php
session_start();
require_once('config/constants.php');

$objDb = new DbConnect();
$conn = $objDb->connect();

 
$user_id = $_SESSION['user_id'] ?? null;

// Handle form submission (AJAX POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($user_id === null) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        echo json_encode([
            'success' => false,
            'redirect' => 'login.php',
            'message' => 'You must be logged in to submit a complaint.'
        ]);
        exit;
    } else {
        header('Content-Type: application/json');

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (!$subject || !$message) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO complaints (user_id, subject, message) VALUES (?, ?, ?)");
            if ($stmt->execute([$user_id, $subject, $message])) {
                echo json_encode(['success' => true, 'message' => 'Your complaint has been submitted.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error submitting complaint.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Fetch contact info (assuming only one row)
$contactSql = "SELECT * FROM contact_info ORDER BY id DESC LIMIT 1";
$contactStmt = $conn->prepare($contactSql);
$contactStmt->execute();
$contact = $contactStmt->fetch(PDO::FETCH_ASSOC);

// Fetch previous complaints of logged in user, latest first
$complaintSql = "SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($complaintSql);
$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<?php include('partials-front/menu.php'); ?>

<div class="bg-gray-50 text-gray-800 p-6 mt-5 flex flex-col md:flex-row gap-8 max-w-8xl mx-auto">

    <!-- Left: Contact info + map -->
    <div class="flex-1 space-y-6">

        <!-- Contact Info -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-4">Contact Information</h2>
            <?php if ($contact): ?>
                <p><strong>Email:</strong>chakrobak_jkkniu@gmail.com</p>
                <p><strong>Phone:</strong>01750104557</p>
                <p><strong>Address:</strong> 
                     Jatiya Kabi Kazi Nazrul Islam, Trishal, Mymensingh
                </p>
                <p class="mt-2 space-x-4">
                    <?php if ($contact['facebook']): ?>
                        <a href="<?= htmlspecialchars($contact['facebook']) ?>" target="_blank" class="text-blue-600 hover:underline">Facebook</a>
                    <?php endif; ?>
                    <?php if ($contact['twitter']): ?>
                        <a href="<?= htmlspecialchars($contact['twitter']) ?>" target="_blank" class="text-blue-400 hover:underline">Twitter</a>
                    <?php endif; ?>
                    <?php if ($contact['instagram']): ?>
                        <a href="<?= htmlspecialchars($contact['instagram']) ?>" target="_blank" class="text-pink-500 hover:underline">Instagram</a>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p>No contact information available.</p>
            <?php endif; ?>
        </div>

        <!-- Map -->
        <?php if ($contact && $contact['map_embed_url']): ?>
            <div class="w-full rounded overflow-hidden shadow">
            
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3628.20842333096!2d90.37261777423659!3d24.582002256345568!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3756417d5fe9a2a3%3A0xc4807b9570837651!2sJatiya%20Kabi%20Kazi%20Nazrul%20Islam%20University!5e0!3m2!1sen!2sbd!4v1757217915931!5m2!1sen!2sbd"
                    width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        <?php endif; ?>

    </div>

    <!-- Right: Complaint form + previous complaints -->
    <div class="flex-1 space-y-6">

        <!-- Complaint Form -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-semibold mb-4">Send Us a Complaint</h2>
            
           <div id="formMessage" class="mt-4 text-center"></div>

            <form  id="complaintForm" class="space-y-4">
                <div>
                    <label for="subject" class="block mb-1 font-medium">Subject</label>
                    <input type="text" name="subject" id="subject" required class="w-full border px-3 py-2 rounded" />
                </div>
                <div>
                    <label for="message" class="block mb-1 font-medium">Message</label>
                    <textarea name="message" id="message" rows="5" required class="w-full border px-3 py-2 rounded"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">Submit Complaint</button>
            </form>
        </div>

       <div class="bg-white p-6 rounded shadow max-h-[400px] overflow-y-auto">
            <h2 class="text-2xl font-semibold mb-6 border-b pb-2">Previous Complaints & Responses</h2>

            <?php if (empty($complaints)): ?>
                <p class="text-center text-gray-500">You have not submitted any complaints yet.</p>
            <?php else: ?>
                <table class="min-w-full table-auto border-collapse border border-gray-300">
                    <thead class="bg-gradient-to-r from-purple-500 to-indigo-600 text-white">
                        <tr>
                            <th  class="px-4 py-2">SN</th>
                            <th class="py-3 px-4 text-left">Subject</th>
                            <th class="py-3 px-4 text-left">Complaint</th>
                            <th class="py-3 px-4 text-left">Submitted On</th>
                        </tr>
                    </thead>

                   <tbody>
                        <?php 
                        $count = 1; // initialize counter before loop
                        foreach ($complaints as $comp): ?>
                            <!-- Complaint row -->
                            <tr class="bg-white hover:bg-gray-50 transition">
                                <td rowspan="2" class="border px-4 py-3 whitespace-nowrap text-gray-700 font-semibold
                                align-top text-center"><?= $count ?></td>
                                <td class="border px-4 py-3 font-semibold text-gray-900 align-top text-justify"><?= htmlspecialchars($comp['subject']) ?></td>
                                <td class="border px-4 py-3 text-gray-700 align-top text-justify"><?= nl2br(htmlspecialchars($comp['message'])) ?></td>
                                <td class="border px-4 py-3 whitespace-nowrap text-gray-600 align-top"><?= date('d M Y, h:i A', strtotime($comp['created_at'])) ?></td>
                            </tr>

                            <!-- Admin response row -->
                            <tr class="bg-gray-50 border-t-0"> 
                                <td class="border border-t-0 px-4 py-3 italic" colspan="3">
                                    <?php if (!empty($comp['admin_response'])): ?>
                                        <strong class="text-green-800">Admin Response:</strong>
                                        <?= nl2br(htmlspecialchars($comp['admin_response'])) ?>
                                        <?php if (!empty($comp['responded_at'])): ?>
                                           <div class="text-sm font-bold text-gray-600 mt-1">
                                                Responded on <?= date('d M Y, h:i A', strtotime($comp['responded_at'])) ?>
                                            </div>

                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">No response yet</span>
                                    <?php endif; ?>
                                </td>
                                 
                            </tr>
                        <?php 
                        $count++; // increment counter
                        endforeach; 
                        ?>
                    </tbody>

                </table>
            <?php endif; ?>
    </div>
    
    </div>

</div>

<script>
    document.getElementById('complaintForm').addEventListener('submit', function(e) {
        e.preventDefault(); // stop form normal submit

        const subject = this.subject.value.trim();
        const message = this.message.value.trim();
        const formMessage = document.getElementById('formMessage');

        if (!subject || !message) {
            formMessage.textContent = 'Please fill in all fields.';
            formMessage.style.color = 'red';
            return;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('subject', subject);
        formData.append('message', message);

        fetch('contact.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())  // parse JSON here
        .then(data => {
             if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.success) {
                formMessage.textContent = data.message;    // use message from PHP
                formMessage.style.color = 'green';
                e.target.reset();
            } else {
                formMessage.textContent = data.message;    // show error message from PHP
                formMessage.style.color = 'red';
            }
        })
        .catch(() => {
            formMessage.textContent = 'Error submitting complaint.';
            formMessage.style.color = 'red';
        });

    });
</script>


<?php include('partials-front/footer.php'); ?>



  