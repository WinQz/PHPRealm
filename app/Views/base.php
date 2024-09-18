<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPRealm - Landing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">PHPRealm</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (session()->has('user')): ?>
                        <!-- User is logged in -->
                        <li class="nav-item">
                            <a class="nav-link" href="/welcome">Welcome</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">Logout</a>
                        </li>
                    <?php else: ?>
                        <!-- User is not logged in -->
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="bg-dark text-light py-5 text-center" style="background-image: url('https://via.placeholder.com/1920x600'); background-size: cover;">
        <div class="container">
            <?php if (session()->has('user')): ?>
                <!-- User is logged in -->
                <div class="container">
                    <h1 class="display-4">Welcome, <?= $user->username; ?>!</h1>
                    <p class="lead">Your journey in PHPRealm begins here!</p>
                </div>
            <?php else: ?>
                <!-- User is not logged in -->
                <h1 class="display-4">Welcome to PHPRealm</h1>
                <p class="lead">An Open-Source MMORPG built with PHP and CodeIgniter 4!</p>
                <a href="/auth/login" class="btn btn-primary btn-lg m-2">Login</a>
                <a href="/auth/register" class="btn btn-secondary btn-lg m-2">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 PHPRealm. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>