<?php
session_start();
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portfolio</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Student Portfolio</h1>
            <p>I'm a passionate web development student showcasing my projects and skills.</p>
            <a href="project.php" class="btn">View My Work</a>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>
