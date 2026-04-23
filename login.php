<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid login credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Student Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="d-flex align-items-center justify-content-center" style="min-height: 90vh; background: #f8f9fa;">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-body p-5">
                <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt me-2"></i>Login</h2>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post">
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>

                <p class="text-center mt-3">
                    Don’t have an account? <a href="register.php" class="text-decoration-none">Register here</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
