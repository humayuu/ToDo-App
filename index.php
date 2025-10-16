<?php
session_start();
if (isset($_SESSION['loggedIn']) == true) {
    header('Location: ../tasks/index.php');
    exit;
}

require 'config/db.php';

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmitted'])) {
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        header('Location: ' . basename(__FILE__) . '?csrfError=1');
        exit;
    }

    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = filter_var(trim($_POST['password']));

    if (empty($email) || empty($password)) {
        header('Location: ' . basename(__FILE__) . '?filedError=1');
        exit;
    }

    try {
        $stmt = $conn->prepare('SELECT * FROM users_tbl WHERE user_email = :uemail');
        $stmt->bindParam(':uemail', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user) {
            header('Location: ' . basename(__FILE__) . '?emailError=1');
            exit;
        }

        if (!password_verify($password, $user['user_password'])) {
            header('Location: ' . basename(__FILE__) . '?passwordError=1');
            exit;
        }

        // Store user data in session
        $_SESSION['loggedIn'] = true;
        $_SESSION['userId'] = $user['id'];
        $_SESSION['userName'] = $user['user_fullname'];
        $_SESSION['userEmail'] = $user['user_email'];

        header('Location: tasks/index.php?loginSuccess=1');
        exit;

    } catch (Exception $e) {
        throw new Exception('Login Error ' . $e->getMessage());
    }

}

require 'includes/header.php';
?>

<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <!-- Logo/Brand Section -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1 text-dark">To-Do List</h2>
                    <p class="text-muted mb-0">Manage your tasks efficiently</p>
                </div>

                <!-- Login Card -->
                <div class="card shadow border-0">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="card-title text-center mb-2 fw-bold">Welcome Back</h3>
                        <p class="text-center text-muted mb-4">Please login to your account</p>
                        <?php
                        if (isset($_GET['filedError']) && $_GET['filedError'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     All Filed are required.
                                  </div>';
                        } elseif (isset($_GET['invalidCSRF']) && $_GET['invalidCSRF'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Invalid CSRF token.
                                  </div>';
                        } elseif (isset($_GET['emailError']) && $_GET['emailError'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Wrong password or email.
                                  </div>';
                        } elseif (isset($_GET['passwordError']) && $_GET['passwordError'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Wrong password or email.
                                  </div>';
                        }
                        ?>

                        <form method="post" action="<?php echo htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf"
                                value="<?php echo htmlspecialchars($_SESSION['__csrf']) ?>">
                            <!-- Email Input -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="your.email@example.com">
                            </div>

                            <!-- Password Input -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter your password">
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <a href="#" class="text-decoration-none">Forgot password?</a>
                            </div>

                            <!-- Login Button -->
                            <div class="d-grid mb-3">
                                <button name="isSubmitted" type="submit" class="btn btn-dark btn-lg">
                                    Login
                                </button>
                            </div>
                        </form>

                        <!-- Registration Link -->
                        <div class="text-center">
                            <p class="mb-0 text-muted">Don't have an account?
                                <a href="registration.php" class="text-decoration-none">
                                    Create Account
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require 'includes/footer.php' ?>
    <?php $conn = null; ?>
</body>