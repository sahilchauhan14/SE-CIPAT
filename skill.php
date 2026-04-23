<?php
session_start();
require 'config.php';

// Always show only owner’s skills
$owner = $conn->query("SELECT id FROM users WHERE is_owner = 1 LIMIT 1")->fetch_assoc();
$owner_id = $owner['id'];

$skills = $conn->query("SELECT * FROM skills WHERE user_id = $owner_id");
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
} else {
    $uid = $conn->query("SELECT id FROM users WHERE is_owner = 1 LIMIT 1")->fetch_assoc()['id'];
}

$skills = $conn->query("SELECT * FROM skills WHERE user_id = $uid");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skills - Student Portfolio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="section">
    <div class="container">
        <h2>My Skills</h2>
        <div class="skills-container">
            <?php while ($s = $skills->fetch_assoc()): ?>
                <div class="skill">
                    <div class="skill-info">
                        <span><?php echo htmlspecialchars($s['name']); ?></span>
                        <span><?php echo $s['level']; ?>%</span>
                    </div>
                    <div class="skill-bar">
                        <div class="skill-progress" style="width: <?php echo $s['level']; ?>%"></div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
