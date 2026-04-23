<?php
session_start();
require 'config.php';

// redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$password_error = $password_success = "";

/* ---------- Helper: safe file upload ---------- */
function upload_file($file_input, $allowed_ext, $target_dir, $prefix) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] !== UPLOAD_ERR_OK) {
        return [false, null, "No file uploaded"];
    }

    $name = $_FILES[$file_input]['name'];
    $tmp  = $_FILES[$file_input]['tmp_name'];
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        return [false, null, "Invalid file type"];
    }

    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return [false, null, "Failed to create directory"];
        }
    }

    $newname = $prefix . "_" . $user_id . "_" . time() . "." . $ext;
    $target  = rtrim($target_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newname;

    if (move_uploaded_file($tmp, $target)) {
        return [true, $target, null];
    } else {
        return [false, null, "Failed to move uploaded file"];
    }
}

/* ---------- Fetch latest user data helper ---------- */
function fetch_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res;
}

/* ---------- Handle POST actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // PROFILE UPDATE (owner or self)
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $bio       = trim($_POST['bio'] ?? '');
        $location  = trim($_POST['location'] ?? '');
        $website   = trim($_POST['website'] ?? '');

        // avatar upload (optional)
        $avatar_path = null;
        if (!empty($_FILES['avatar']['name'])) {
            list($ok, $path, $err) = upload_file('avatar', ['jpg','jpeg','png','gif'], 'uploads/avatars', 'avatar');
            if ($ok) {
                $avatar_path = $path;
            } else {
                // set an error message into $profile_error (but continue)
                $profile_error = $err;
            }
        }

        // Build the update depending on whether avatar was uploaded
        if ($avatar_path) {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, bio=?, location=?, website=?, avatar=? WHERE id=?");
            $stmt->bind_param("ssssssi", $full_name, $username, $bio, $location, $website, $avatar_path, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, bio=?, location=?, website=? WHERE id=?");
            $stmt->bind_param("sssssi", $full_name, $username, $bio, $location, $website, $user_id);
        }
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php");
        exit;
    }

    // ADD PROJECT
    if (isset($_POST['add_project'])) {
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $img   = trim($_POST['image_url'] ?? '');

        $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, image_url) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $user_id, $title, $desc, $img);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php");
        exit;
    }

    // UPDATE PROJECT
    if (isset($_POST['update_project'])) {
        $pid = (int)($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $img   = trim($_POST['image_url'] ?? '');

        $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, image_url=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii", $title, $desc, $img, $pid, $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php");
        exit;
    }

    // ADD SKILL
    if (isset($_POST['add_skill'])) {
        $name = trim($_POST['skill_name'] ?? '');
        $level = (int)($_POST['skill_level'] ?? 0);
        $stmt = $conn->prepare("INSERT INTO skills (user_id, name, level) VALUES (?,?,?)");
        $stmt->bind_param("isi", $user_id, $name, $level);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php");
        exit;
    }

    // UPDATE SKILL
    if (isset($_POST['update_skill'])) {
        $sid = (int)($_POST['skill_id'] ?? 0);
        $name = trim($_POST['skill_name'] ?? '');
        $level = (int)($_POST['skill_level'] ?? 0);
        $stmt = $conn->prepare("UPDATE skills SET name=?, level=? WHERE id=? AND user_id=?");
        $stmt->bind_param("siii", $name, $level, $sid, $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php");
        exit;
    }

    // CHANGE PASSWORD
    if (isset($_POST['change_password'])) {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($old, $row['password'])) {
            $password_error = "Old password is incorrect.";
        } elseif ($new !== $confirm) {
            $password_error = "New passwords do not match.";
        } elseif (strlen($new) < 6) {
            $password_error = "Password must be at least 6 characters.";
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hash, $user_id);
            $stmt->execute();
            $stmt->close();
            $password_success = "Password updated successfully.";
        }
    }

    // UPDATE SOCIALS (only if owner)
    if (isset($_POST['update_socials'])) {
        // re-fetch user to check is_owner
        $user_check = fetch_user($conn, $user_id);
        if (!empty($user_check['is_owner']) && intval($user_check['is_owner']) === 1) {
            $github = trim($_POST['github'] ?? '');
            $linkedin = trim($_POST['linkedin'] ?? '');
            $twitter = trim($_POST['twitter'] ?? '');
            $instagram = trim($_POST['instagram'] ?? '');
            $stmt = $conn->prepare("UPDATE users SET github=?, linkedin=?, twitter=?, instagram=? WHERE id=?");
            $stmt->bind_param("ssssi", $github, $linkedin, $twitter, $instagram, $user_id);
            $stmt->execute();
            $stmt->close();
            header("Location: dashboard.php");
            exit;
        }
    }

} // end POST handling

/* ---------- Handle GET deletes ---------- */
if (isset($_GET['delete_project'])) {
    $pid = (int)$_GET['delete_project'];
    $stmt = $conn->prepare("DELETE FROM projects WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $pid, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['delete_skill'])) {
    $sid = (int)$_GET['delete_skill'];
    $stmt = $conn->prepare("DELETE FROM skills WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $sid, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}

/* ---------- Re-fetch fresh data for display ---------- */
$user = fetch_user($conn, $user_id);
$projects = $conn->query("SELECT * FROM projects WHERE user_id = $user_id");
$skills = $conn->query("SELECT * FROM skills WHERE user_id = $user_id");

// If owner, fetch messages & socials for display (optional)
$is_owner = !empty($user['is_owner']) && intval($user['is_owner']) === 1;
$messages = $is_owner ? $conn->query("SELECT * FROM messages WHERE user_id = $user_id ORDER BY created_at DESC") : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard - Student Portfolio</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h2>

    <div class="row g-4">

        <!-- Profile / About Editor (owner-only editing for public about page can be gated by $is_owner) -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-user"></i> My Profile
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div>
                            <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://via.placeholder.com/120'; ?>"
                                 class="rounded-circle" width="100" height="100" alt="avatar">
                        </div>
                        <div class="flex-grow-1">
                            <form method="post" enctype="multipart/form-data">
                                <input type="text" name="full_name" class="form-control mb-2" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Full name" required>
                                <input type="text" name="username" class="form-control mb-2" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" placeholder="Username">
                                <textarea name="bio" class="form-control mb-2" placeholder="Short bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                <input type="text" name="location" class="form-control mb-2" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="Location">
                                <input type="url" name="website" class="form-control mb-2" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>" placeholder="Website">
                                <input type="file" name="avatar" class="form-control mb-2" accept="image/*">
                                <button type="submit" name="update_profile" class="btn btn-primary w-100">Save Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change password -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <i class="fa fa-lock"></i> Change Password
                </div>
                <div class="card-body">
                    <?php if (!empty($password_error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($password_error); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($password_success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($password_success); ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <input type="password" name="old_password" class="form-control mb-2" placeholder="Current password" required>
                        <input type="password" name="new_password" class="form-control mb-2" placeholder="New password" required>
                        <input type="password" name="confirm_password" class="form-control mb-2" placeholder="Confirm new password" required>
                        <button type="submit" name="change_password" class="btn btn-warning w-100">Update Password</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Projects -->
    <div class="card shadow-sm my-4">
        <div class="card-header bg-success text-white">
            <i class="fa fa-code"></i> My Projects
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php while ($p = $projects->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if (!empty($p['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($p['image_url']); ?>" class="card-img-top" style="object-fit:cover; height:180px;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($p['title']); ?></h5>
                            <p><?php echo htmlspecialchars($p['description']); ?></p>

                            <form method="post" class="mb-2">
                                <input type="hidden" name="project_id" value="<?php echo (int)$p['id']; ?>">
                                <input type="text" name="title" class="form-control mb-2" value="<?php echo htmlspecialchars($p['title']); ?>">
                                <textarea name="description" class="form-control mb-2"><?php echo htmlspecialchars($p['description']); ?></textarea>
                                <input type="text" name="image_url" class="form-control mb-2" value="<?php echo htmlspecialchars($p['image_url']); ?>">
                                <button type="submit" name="update_project" class="btn btn-primary btn-sm">Update</button>
                            </form>

                            <a href="?delete_project=<?php echo (int)$p['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <hr>
            <h5>Add Project</h5>
            <form method="post">
                <input type="text" name="title" class="form-control mb-2" placeholder="Project title" required>
                <textarea name="description" class="form-control mb-2" placeholder="Project description" required></textarea>
                <input type="text" name="image_url" class="form-control mb-2" placeholder="Image URL (optional)">
                <button type="submit" name="add_project" class="btn btn-success w-100">Add Project</button>
            </form>
        </div>
    </div>

    <!-- Skills -->
    <div class="card shadow-sm my-4">
        <div class="card-header bg-info text-white">
            <i class="fa fa-lightbulb"></i> My Skills
        </div>
        <div class="card-body">
            <?php while ($s = $skills->fetch_assoc()): ?>
                <div class="mb-3">
                    <label><?php echo htmlspecialchars($s['name']); ?> (<?php echo (int)$s['level']; ?>%)</label>
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo (int)$s['level']; ?>%"><?php echo (int)$s['level']; ?>%</div>
                    </div>

                    <form method="post" class="d-flex gap-2 mb-2">
                        <input type="hidden" name="skill_id" value="<?php echo (int)$s['id']; ?>">
                        <input type="text" name="skill_name" class="form-control" value="<?php echo htmlspecialchars($s['name']); ?>">
                        <input type="number" name="skill_level" class="form-control" value="<?php echo (int)$s['level']; ?>" min="1" max="100">
                        <button type="submit" name="update_skill" class="btn btn-primary btn-sm">Update</button>
                        <a href="?delete_skill=<?php echo (int)$s['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </form>
                </div>
            <?php endwhile; ?>

            <hr>
            <h5>Add Skill</h5>
            <form method="post" class="d-flex gap-2">
                <input type="text" name="skill_name" class="form-control" placeholder="Skill name" required>
                <input type="number" name="skill_level" class="form-control" placeholder="Level (%)" min="1" max="100" required>
                <button type="submit" name="add_skill" class="btn btn-info">Add Skill</button>
            </form>
        </div>
    </div>

    <?php if ($is_owner): ?>
        <!-- Messages (owner only) -->
        <div class="card shadow-sm my-4">
            <div class="card-header bg-secondary text-white">
                <i class="fa fa-envelope"></i> Messages
            </div>
            <div class="card-body">
                <?php if ($messages && $messages->num_rows > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php while ($m = $messages->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['name']); ?></td>
                                <td><?php echo htmlspecialchars($m['email']); ?></td>
                                <td><?php echo htmlspecialchars($m['subject']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($m['message'])); ?></td>
                                <td><?php echo htmlspecialchars($m['created_at']); ?></td>
                                <td><a href="?delete_message=<?php echo (int)$m['id']; ?>" class="btn btn-sm btn-danger">Delete</a></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No messages yet.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
