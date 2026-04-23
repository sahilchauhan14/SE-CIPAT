<header>
    <div class="container">
        <nav>
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <span>StudentPortfolio</span>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="project.php">Projects</a></li>
                <li><a href="skill.php">Skills</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php else: ?>
                    <li><a href="dashboard.php"><?php echo $_SESSION['username']; ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<head>
    <meta charset="UTF-8">
    <title>Student Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <!-- Font Awesome (Icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
