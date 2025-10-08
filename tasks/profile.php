<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['loggedIn']) == false) {
    header('Location: ../index.php?loginFirst=1');
    exit;
}






require '../includes/header.php'; ?>

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
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <h1 class="p-2">Update Profile</h1>
        <form class="card p-5">
            <!-- Full Name -->
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" placeholder="John Doe" required>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="your.email@example.com"
                    required>
            </div>

            <!-- Currnt Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Create a strong password" required>
                <div class="form-text">Must be at least 8 characters long</div>
            </div>

            <!-- New Password -->
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password"
                    placeholder="Create a new strong password" required>
                <div class="form-text">Must be at least 8 characters long</div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="confirmPassword" class="form-label">New Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                    placeholder="Re-enter your new password" required>
            </div>
            <!-- Update Button -->
            <div class=" mb-3">
                <button type="submit" class="btn btn-dark btn-lg">
                    Update Profile
                </button>
                <a href="" type="submit" class="btn btn-outline-dark ">
                    ← Back
                </a>
            </div>

        </form>
    </div>

    <?php require '../includes/footer.php'; ?>