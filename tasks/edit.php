<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['loggedIn']) == false) {
    header('Location: ../index.php?loginFirst=1');
    exit;
}

// Generate CSRF Token
if (empty($_SESSION['__csrf'])) {
    $_SESSION['__csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        header('Location: ' . basename(__FILE__) . '?csrfError=1');
        exit;
    }

    $id = filter_var(trim($_POST['id']), FILTER_VALIDATE_INT);
    $task = filter_var(trim($_POST['task']), FILTER_SANITIZE_SPECIAL_CHARS);

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("UPDATE task_tbl SET task = :task WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':task', $task);
        $result = $stmt->execute();

        if ($result) {
            $conn->commit();

            // Redirected to Home Page
            header('Location: index.php?updateSuccess=1');
            exit;
        }

    } catch (Exception $e) {
        $conn->rollBack();
        throw new Exception('Task Update Error ' . $e->getMessage());
    }

}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var(trim($_POST['id']), FILTER_VALIDATE_INT);
    $sql = $conn->prepare("SELECT * FROM task_tbl WHERE id = '$id'");
    $sql->execute();
    $task = $sql->fetch();
}




require '../includes/header.php';
?>

<body class="bg-light">
    <!-- User Info Section -->
    <div class="bg-white shadow-sm border-bottom">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 60px; height: 60px; font-size: 15px; font-weight: bold;">
                        <?php echo htmlspecialchars($_SESSION['userName']) ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">
                            <?php echo htmlspecialchars($_SESSION['userName']) ?>
                        </h6>
                        <small class="text-muted"><?php echo htmlspecialchars($_SESSION['userEmail']) ?></small>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userMenu"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Menu
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="fw-bold mb-1">My Tasks</h1>
                <p class="text-muted mb-0">Manage and organize your daily tasks</p>
            </div>
        </div>

        <!-- Add Task Form -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title fw-bold mb-0">Edit Task</h5>
                            <!-- Back Button -->
                            <a href="index.php" class="btn btn-outline-secondary">
                                ← Back
                            </a>
                        </div>
                        <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($task['id']) ?>">
                            <div class="row g-3">
                                <div class="col-md-9">
                                    <input type="text" class="form-control form-control-lg"
                                        placeholder="Enter a task here..." name="task"
                                        value="<?= htmlspecialchars($task['task']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <button name="submit" type="submit" class="btn btn-dark btn-lg w-100">Update
                                        Task</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php require '../includes/footer.php'; ?>
    <?php $conn = null; ?>