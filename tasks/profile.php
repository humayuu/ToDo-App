<?php
session_start();
require '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: ../index.php?loginFirst=1');
    exit;
}

// Check database connection
if (!isset($conn)) {
    die('Database connection failed');
}

// Fetch User Data
$user = null;
if (isset($_SESSION['userId'])) {
    $id = (int)$_SESSION['userId'];
    try {
        $sql = $conn->prepare('SELECT * FROM users_tbl WHERE id = :id');
        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        $user = $sql->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            session_destroy();
            header('Location: ../index.php?sessionExpired=1');
            exit;
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        die('An error occurred. Please try again later.');
    }
}

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

// Initialize error array for displaying messages
$errors = [];
$success = false;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])) {
    
    // Validate CSRF Token
    if (!isset($_POST['__csrf']) || !hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        $errors[] = 'Security validation failed. Please try again.';
    } else {
        
        // Sanitize and validate inputs
        $name = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // Validation: Required fields
        if (empty($name)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Full name must not exceed 100 characters.';
        }
        
        // Validate email
        if (empty($email)) {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strlen($email) > 255) {
            $errors[] = 'Email must not exceed 255 characters.';
        }
        
        // Validate current password
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required to make changes.';
        } elseif (!password_verify($currentPassword, $user['user_password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        
        // Determine if password change is requested
        $changePassword = !empty($newPassword) || !empty($confirmPassword);
        $finalPassword = $user['user_password']; // Keep old password by default
        
        if ($changePassword) {
            // Validate new password
            if (empty($newPassword)) {
                $errors[] = 'New password is required.';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters long.';
            }
            
            if (empty($confirmPassword)) {
                $errors[] = 'Please confirm your new password.';
            }
            
            if (!empty($newPassword) && !empty($confirmPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match.';
                } else {
                    // Hash the new password
                    $finalPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }
        }
        
        // Check for duplicate email (if email is being changed)
        if (empty($errors) && $email !== $user['user_email']) {
            try {
                $emailCheck = $conn->prepare('SELECT id FROM users_tbl WHERE user_email = :email AND id != :id');
                $emailCheck->bindParam(':email', $email, PDO::PARAM_STR);
                $emailCheck->bindParam(':id', $id, PDO::PARAM_INT);
                $emailCheck->execute();
                
                if ($emailCheck->fetch()) {
                    $errors[] = 'This email address is already in use by another account.';
                }
            } catch (PDOException $e) {
                error_log('Email check error: ' . $e->getMessage());
                $errors[] = 'An error occurred. Please try again.';
            }
        }
        
        // Update database if no errors
        if (empty($errors)) {
            try {
                $conn->beginTransaction();
                
                $stmt = $conn->prepare('UPDATE users_tbl 
                                       SET user_fullname = :fname,
                                           user_email = :uemail,
                                           user_password = :upassword
                                       WHERE id = :id');
                
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':fname', $name, PDO::PARAM_STR);
                $stmt->bindParam(':uemail', $email, PDO::PARAM_STR);
                $stmt->bindParam(':upassword', $finalPassword, PDO::PARAM_STR);
                
                $result = $stmt->execute();
                
                if ($result) {
                    // Update session data
                    $_SESSION['userName'] = $name;
                    $_SESSION['userEmail'] = $email;
                    
                    // Regenerate CSRF token after successful update
                    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
                    
                    // Log the update (optional but recommended)
                    error_log("Profile updated for user ID: $id");
                    
                    $conn->commit();
                    
                    // Set success flag
                    $success = true;
                    
                    // Update local user variable to reflect changes
                    $user['user_fullname'] = $name;
                    $user['user_email'] = $email;
                    $user['user_password'] = $finalPassword;
                    
                } else {
                    $conn->rollBack();
                    $errors[] = 'Failed to update profile. Please try again.';
                }
                
            } catch (PDOException $e) {
                $conn->rollBack();
                error_log('Profile update error: ' . $e->getMessage());
                $errors[] = 'An error occurred while updating your profile. Please try again.';
            }
        }
    }
}

require '../includes/header.php'; ?>

<body class="bg-light">
    <!-- User Info Section -->
    <div class="bg-white shadow-sm border-bottom">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 60px; height: 60px; font-size: 15px; font-weight: bold;">
                        <?= htmlspecialchars($_SESSION['userName']) ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">
                            <?= htmlspecialchars($_SESSION['userName']) ?>
                        </h6>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['userEmail']) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <h1 class="p-2">Update Profile</h1>

        <!-- Success Message -->
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Your profile has been updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Please fix the following issues:
            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="card p-5">
            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">

            <!-- Full Name -->
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control <?= isset($errors[0]) && strpos($errors[0], 'name') !== false ? 'is-invalid' : '' ?>"
                    id="fullname" name="fullname" placeholder="John Doe" maxlength="100"
                    value="<?= htmlspecialchars($user['user_fullname']) ?>" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com"
                    maxlength="255" value="<?= htmlspecialchars($user['user_email']) ?>" required>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Security</h5>

            <!-- Current Password -->
            <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password <span
                        class="text-danger">*</span></label>
                <input type="password" class="form-control" id="currentPassword" name="currentPassword"
                    placeholder="Enter your current password" autocomplete="current-password" required>
                <div class="form-text">Required to confirm your identity</div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3">Change Password (Optional)</h5>
            <p class="text-muted small">Leave blank if you don't want to change your password</p>

            <!-- New Password -->
            <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword"
                    placeholder="Create a new strong password" autocomplete="new-password" minlength="8">
                <div class="form-text">Must be at least 8 characters long</div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                    placeholder="Re-enter your new password" autocomplete="new-password" minlength="8">
            </div>

            <!-- Update Button -->
            <div class="mb-3">
                <button type="submit" name="isSubmitted" class="btn btn-dark btn-lg">
                    Update Profile
                </button>
                <a href="index.php" class="btn btn-outline-dark">
                    ← Back
                </a>
            </div>

        </form>
    </div>

    <?php require '../includes/footer.php'; ?>
</body>

</html>

<?php $conn = null ?>