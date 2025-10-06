<?php require '../includes/header.php'; ?>

<body class="bg-light">
    <!-- User Info Section -->
    <div class="bg-white shadow-sm border-bottom">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 50px; height: 50px; font-size: 20px; font-weight: bold;">
                        JD
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">John Doe</h6>
                        <small class="text-muted">john.doe@example.com</small>
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
                        <li><a class="dropdown-item text-danger" href="#">Logout</a></li>
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
                        <form>
                            <div class="row g-3">
                                <div class="col-md-9">
                                    <input type="text" class="form-control form-control-lg"
                                        placeholder="Enter a task here..." name="task">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-dark btn-lg w-100">Add Task</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Tasks Table -->
        <div class="row">
            <div class="col">
                <div class="card shadow border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
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
                                    <tr>
                                        <th scope="row" class="px-4 py-3">2</th>
                                        <td class="py-3">Review pull requests</td>
                                        <td class="py-3">
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                        <td class="py-3">Oct 5, 2025</td>
                                        <td class="py-3 text-center">
                                            <button class="btn btn-sm btn-outline-secondary me-1"
                                                disabled>Complete</button>
                                            <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require '../includes/footer.php'; ?>