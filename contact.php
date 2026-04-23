<?php
session_start();
require 'config.php';

// Get portfolio owner
$owner = $conn->query("SELECT id, email FROM users WHERE is_owner = 1 LIMIT 1")->fetch_assoc();
$owner_id = $owner['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO messages (user_id, name, email, subject, message) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issss", $owner_id, $name, $email, $subject, $message);
    $stmt->execute();

    // Send email
    $to = $owner['email'];
    $headers = "From: $email\r\nReply-To: $email\r\n";
    mail($to, "New Contact Message: $subject", $message, $headers);

    $success = "✅ Your message has been sent successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact - Student Portfolio</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/3c9e9d9a4f.js" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .contact-box {
            max-width: 600px;   /* Fixed box size */
            margin: auto;
            margin-top: 60px;
            margin-bottom: 60px;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="container">
    <div class="contact-box">
        <h2 class="text-center mb-4"><i class="fas fa-paper-plane me-2"></i>Contact Me</h2>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Your Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope me-2"></i>Your Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-tag me-2"></i>Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Message subject" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-comment-dots me-2"></i>Message</label>
                <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Send Message
                </button>
            </div>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
