<?php
session_start();
require 'config.php';

// Determine which user's projects to show:
//  - if a user is logged in -> show that user's projects
//  - otherwise -> fall back to the portfolio owner (is_owner = 1)

$uid = 0;
if (!empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
} else {
    // fetch owner id safely
    $stmt = $conn->prepare("SELECT id FROM users WHERE is_owner = 1 LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();
    $ownerRow = $res->fetch_assoc();
    $stmt->close();
    $uid = $ownerRow ? (int)$ownerRow['id'] : 0;
}

// If no user (no owner and not logged in), $uid will be 0 -> no projects
$projects = [];
if ($uid > 0) {
    $stmt = $conn->prepare("SELECT id, title, description, image_url FROM projects WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $projects = $stmt->get_result(); // mysqli_result or false
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Projects - Student Portfolio</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>My Projects</h2>
    </div>

    <?php if ($uid === 0): ?>
      <p>No projects to show.</p>
    <?php else: ?>
      <div class="projects-grid">
        <?php if ($projects && $projects->num_rows > 0): ?>
          <?php while ($p = $projects->fetch_assoc()): ?>
            <div class="project-card">
              <div class="project-image">
                <?php if (!empty($p['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($p['image_url']); ?>" alt="<?php echo htmlspecialchars($p['title']); ?>">
                <?php else: ?>
                  <img src="https://via.placeholder.com/300x200" alt="No image">
                <?php endif; ?>
              </div>
              <div class="project-info">
                <h3><?php echo htmlspecialchars($p['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No projects found for this user.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
