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
$userId = htmlspecialchars($_SESSION['userId']);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!hash_equals($_SESSION['__csrf'], $_POST['__csrf'])) {
        header('Location: ' . basename(__FILE__) . '?csrfError=1');
        exit;
    }

    $task = filter_var(trim($_POST['task']), FILTER_SANITIZE_SPECIAL_CHARS);
    $status = 'pending';

    if (empty($task)) {
        header('Location: ' . basename(__FILE__) . '?filedError=1');
        exit;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare('INSERT INTO task_tbl (user_id, task, status) VALUES (:uId, :utask, :utaskStatus)');
        $stmt->bindParam(':uId', $userId);
        $stmt->bindParam(':utask', $task);
        $stmt->bindParam(':utaskStatus', $status);
        $result = $stmt->execute();
        if ($result) {
            $conn->commit();

            header('Location: ' . htmlspecialchars(basename(__FILE__)) . '?insertSuccessful=1');
            exit;
        }
    } catch (Exception $e) {
        $conn->rollBack();
        throw new Exception('Task inert Error ' . $e->getMessage());
    }

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
                        <?= htmlspecialchars($_SESSION['userName']) ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">
                            <?= htmlspecialchars($_SESSION['userName']) ?>
                        </h6>
                        <small class="text-muted"><?= htmlspecialchars($_SESSION['userEmail']) ?></small>
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
                        <h5 class="card-title mb-3 fw-bold">Add New Task</h5>
                        <form method="post" action="<?= htmlspecialchars(basename(__FILE__)) ?>">
                            <input type="hidden" name="__csrf" value="<?= htmlspecialchars($_SESSION['__csrf']) ?>">
                            <div class="row g-3">
                                <div class="col-md-9">
                                    <input autofocus type="text" class="form-control form-control-lg"
                                        placeholder="Enter a task here..." name="task">
                                </div>
                                <div class="col-md-3">
                                    <button name="submit" type="submit" class="btn btn-dark btn-lg w-100">Add
                                        Task</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $sl = 1;
        $sql = $conn->prepare("SELECT * FROM task_tbl WHERE user_id = :userId");
        $sql->bindParam(':userId', $userId);
        $sql->execute();
        $tasks = $sql->fetchAll();
        ?>
        <!-- Tasks Table -->
        <div class="row">
            <div class="col">
                <div class="card shadow border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <?php if ($tasks): ?>
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="px-4 py-3">#</th>
                                            <th scope="col" class="py-3">Task Description</th>
                                            <th scope="col" class="py-3">Status</th>
                                            <th scope="col" class="py-3">Date Added</th>
                                            <th scope="col" class="py-3 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tasks as $task): ?>
                                            <tr>
                                                <th scope="row" class="px-4 py-3"><?= $sl++ ?></th>
                                                <td class="py-3"><?= htmlspecialchars($task['task']); ?></td>
                                                <td class="py-3">
                                                    <span
                                                        class="badge bg-success p-1"><?= htmlspecialchars($task['status']); ?></span>
                                                </td>
                                                <td class="py-3">Oct 5, 2025</td>
                                                <td class="py-3 text-center">
                                                    <button class="btn btn-sm btn-outline-secondary me-1"
                                                        disabled>Complete</button>
                                                    <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-danger m-5" role="alert">
                                    No Task Found!
                                </div>';
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require '../includes/footer.php'; ?>
    <?php $conn = null; ?>