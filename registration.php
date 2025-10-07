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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isSubmit'])) {
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        header('Location: ' . basename(__FILE__) . '?invalidCSRF=1');
        exit;
    }

    $name = filter_var(trim($_POST['fullname']), FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = filter_var(trim($_POST['password']));
    $confirmPassword = filter_var(trim($_POST['confirmPassword']));

    if (empty($name) || empty($email) || empty($password)) {
        header('Location: ' . basename(__FILE__) . '?filedError=1');
        exit;
    } elseif (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . basename(__FILE__) . '?invalidEmail=1');
        exit;
    } elseif (strlen($password) < 8) {
        header('Location: ' . basename(__FILE__) . '?passwordCharError=1');
        exit;
    } elseif ($password !== $confirmPassword) {
        header('Location: ' . basename(__FILE__) . '?confirmPassword=1');
        exit;
    }

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        // Check if the user is Already exists
        $sql = $conn->prepare('SELECT * FROM users_tbl');
        $sql->execute();
        $users = $sql->fetchAll();
        foreach ($users as $user) {
            if ($email == $user['user_email']) {
                header('Location: ' . basename(__FILE__) . '?emailAlready=1');
                exit;
            }
        }

        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO users_tbl (user_fullname, user_email, user_password) VALUES (:ufname, :uemail, :upassword)');
        $stmt->bindParam(':ufname', $name);
        $stmt->bindParam(':uemail', $email);
        $stmt->bindParam(':upassword', $hashPassword);
        $result = $stmt->execute();
        if ($result) {
            $conn->commit();

            // Redirect to login page
            header('Location: index.php?registerSuccess=1');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        throw new Exception('Registration Error ' . $e->getMessage());
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

                <!-- Registration Card -->
                <div class="card shadow border-0">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="card-title text-center mb-2 fw-bold fs-3">Create Account</h1>
                        <p class="text-center text-muted mb-4">Sign up to get started</p>
                        <?php
                        if (isset($_GET['filedError']) && $_GET['filedError'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     All Filed are required.
                                  </div>';
                        } elseif (isset($_GET['invalidEmail']) && $_GET['invalidEmail'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Invalid Email Address.
                                  </div>';
                        } elseif (isset($_GET['passwordCharError']) && $_GET['passwordCharError'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Password must be in 8 Characters.
                                  </div>';

                        } elseif (isset($_GET['confirmPassword']) && $_GET['confirmPassword'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Password and Confirm Password must be matched.
                                  </div>';
                        } elseif (isset($_GET['invalidCSRF']) && $_GET['invalidCSRF'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Invalid CSRF token.
                                  </div>';
                        } elseif (isset($_GET['emailAlready']) && $_GET['emailAlready'] == 1) {
                            echo '<div class="alert alert-danger" role="alert">
                                     Email is Already exists.
                                  </div>';
                        }

                        ?>

                        <form method="post" action="<?php echo htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf"
                                value="<?php echo htmlspecialchars($_SESSION['__csrf']) ?>">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname"
                                    placeholder="John Doe">
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="your.email@example.com">
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Create a strong password">
                                <div class="form-text">Must be at least 8 characters long</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    placeholder="Re-enter your password">
                            </div>

                            <!-- Register Button -->
                            <div class="d-grid mb-3">
                                <button name="isSubmit" type="submit" class="btn btn-dark btn-lg">Create
                                    Account</button>
                            </div>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="mb-0 text-muted">Already have an account?
                                <a href="index.php" class="text-decoration-none">Login here</a>
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