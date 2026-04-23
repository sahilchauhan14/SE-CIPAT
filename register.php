<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $name, $username, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Registration successful. Please log in.";
        header("Location: login.php");
        exit;
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .auth-box {
            max-width: 420px;   /* Fixed box size */
            margin: auto;
            margin-top: 60px;
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
    <div class="auth-box">
        <h2 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Create Your Account</h2>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post">
            <!-- Full Name -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <!-- Username -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user-circle me-2"></i>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Create a password" required>
            </div>

            <!-- Submit -->
            <div class="d-grid">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-user-plus me-2"></i>Register
                </button>
            </div>
        </form>

        <p class="text-center mt-3">
            Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>
        </p>
    </div>
</section>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
