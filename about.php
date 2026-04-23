<?php
session_start();
require 'config.php';

// Always show only owner’s profile
$result = $conn->query("SELECT * FROM users WHERE is_owner = 1 LIMIT 1");
$user = $result ? $result->fetch_assoc() : null;

if (isset($_SESSION['user_id'])) {
    // Show logged-in user data
    $uid = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $uid LIMIT 1");
} else {
    // Fallback: show owner’s profile if no user is logged in
    $result = $conn->query("SELECT * FROM users WHERE is_owner = 1 LIMIT 1");
}

$user = $result ? $result->fetch_assoc() : null;

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Me - Student Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="section about">
    <div class="container">
        <h2>About Me</h2>
        <div class="about-content">
            <div class="about-text">
                <p><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></p>
                <p><?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : "This portfolio owner has not added a bio yet."; ?></p>
                
                <?php if (!empty($user['website'])): ?>
                    <p><i class="fas fa-globe"></i> 
                        <a href="<?php echo htmlspecialchars($user['website']); ?>" target="_blank">
                            <?php echo htmlspecialchars($user['website']); ?>
                        </a>
                    </p>
                <?php endif; ?>

                <?php if (!empty($user['location'])): ?>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['location']); ?></p>
                <?php endif; ?>
            </div>
            <div class="about-image">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Profile Picture" style="border-radius:10px; max-width:300px;">
                <?php else: ?>
                    <img src="https://via.placeholder.com/300" alt="Default Avatar" style="border-radius:10px;">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
