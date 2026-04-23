<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Student Portfolio</h3>
                <p>A showcase of my skills, projects, and journey as a web development student.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="project.php">Projects</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Me</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Student Portfolio. All rights reserved.</p>
        </div>
    </div>
</footer>
<?php
// fetch portfolio owner
$owner = $conn->query("SELECT github, linkedin, twitter, instagram FROM users WHERE is_owner = 1 LIMIT 1")->fetch_assoc();
?>

<div class="social-links">
    <?php if (!empty($owner['github'])): ?>
        <a href="<?php echo htmlspecialchars($owner['github']); ?>" target="_blank"><i class="fab fa-github"></i></a>
    <?php endif; ?>
    <?php if (!empty($owner['linkedin'])): ?>
        <a href="<?php echo htmlspecialchars($owner['linkedin']); ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
    <?php endif; ?>
    <?php if (!empty($owner['twitter'])): ?>
        <a href="<?php echo htmlspecialchars($owner['twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
    <?php endif; ?>
    <?php if (!empty($owner['instagram'])): ?>
        <a href="<?php echo htmlspecialchars($owner['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
    <?php endif; ?>
</div>

