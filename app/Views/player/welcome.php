<?php $this->extend('base') ?>

<?php $this->section('content') ?>

<style>
    /* Player Banner */
    .player-banner {
        position: relative;
        text-align: center;
        color: white;
    }

    .player-banner-image {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        height: 300px;
        width: 100%;
        background-size: cover;
        background-position: center;
    }

    /* Player Info Styling */
    .player-info {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        background: rgba(0, 0, 0, 0.6);
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        color: white;
        max-width: 90%;
    }

    .player-info h2 {
        margin: 0;
        font-size: 2.5rem;
    }

    .player-info p {
        margin: 0;
        font-size: 1.25rem;
    }

    .play-button {
        margin-top: 0;
        padding: 0.75rem 2rem;
        font-size: 1.25rem;
        border-radius: 25px;
        background-color: #28a745; /* Green color */
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .play-button:hover {
        background-color: #218838; /* Darker green */
        transform: scale(1.05);
    }

    /* Player Stats */
    .player-stats {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        background-color: #f8f9fa; /* Light background for contrast */
        margin-top: 2rem;
    }

    .card-header {
        background-color: #343a40; /* Dark background for header */
        color: white;
        padding: 0.75rem 1.25rem;
        font-size: 1.25rem;
        border-bottom: 1px solid #dee2e6;
    }

    .list-group-item {
        display: flex;
        justify-content: space-between;
        padding: 1rem;
        border: none;
        border-bottom: 1px solid #dee2e6;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .list-group-item strong {
        font-weight: bold;
    }

    .btn-custom {
        border-radius: 25px; /* Rounded button */
        padding: 0.5rem 1.5rem;
        font-size: 1rem;
    }

    .btn-custom.btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-custom.btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
</style>

<!-- Player Stats -->
<section class="container my-5">
    <div class="player-banner d-flex flex-column align-items-center mb-4">
        <div class="player-banner-image" style="background-image: url('https://via.placeholder.com/1200x300');"></div>
        <div class="player-info">
            <h2 class="display-4">Welcome Adventurer, <?= $user->username; ?></h2>
            <p class="lead">Level <?= $user->level; ?> - World ID: <?= $user->world; ?></p>
            <br>
            <a href="/client" target="_blank" class="play-button">Play</a>
        </div>
        
    </div>

    <div class="player-stats card shadow-sm">
        <div class="card-header">
            Player Stats
        </div>
        <div class="card-body">
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Health:</strong>
                    <span><?= $user->health; ?>/<?= $user->max_health; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Mana:</strong>
                    <span><?= $user->mana; ?>/<?= $user->max_mana; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Gold:</strong>
                    <span><?= $user->gold; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <strong>Experience:</strong>
                    <span><?= $user->experience; ?></span>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- News Section -->
<section class="container my-5">
    <h2 class="text-center mb-4">Latest News</h2>
    <div class="row g-4">
        <!-- News Card 1 -->
        <div class="col-md-4">
            <div class="news-card card h-100">
                <img src="https://via.placeholder.com/400x300" class="card-img-top" alt="News Image 1">
                <div class="card-body">
                    <h5 class="card-title">Major Update Released</h5>
                    <p class="card-text">We have just released a major update with new quests, a revamped combat system, and exciting new features!</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Published on Sep 1, 2024</small>
                </div>
            </div>
        </div>

        <!-- News Card 2 -->
        <div class="col-md-4">
            <div class="news-card card h-100">
                <img src="https://via.placeholder.com/400x300" class="card-img-top" alt="News Image 2">
                <div class="card-body">
                    <h5 class="card-title">Upcoming PvP Tournament</h5>
                    <p class="card-text">Get ready for the first-ever PHPRealm PvP tournament! Sign-ups are now open, and the grand prize awaits the champion!</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Published on Sep 10, 2024</small>
                </div>
            </div>
        </div>

        <!-- News Card 3 -->
        <div class="col-md-4">
            <div class="news-card card h-100">
                <img src="https://via.placeholder.com/400x300" class="card-img-top" alt="News Image 3">
                <div class="card-body">
                    <h5 class="card-title">New Regions Unlocked</h5>
                    <p class="card-text">Explore the newly unlocked regions in PHPRealm, featuring new monsters, rare loot, and challenging quests.</p>
                </div>
                <div class="card-footer">
                    <small class="text-muted">Published on Sep 15, 2024</small>
                </div>
            </div>
        </div>
    </div>
</section>

<?php $this->endSection() ?>