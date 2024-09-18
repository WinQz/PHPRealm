<?php $this->extend('base') ?>

<?php $this->section('content') ?>

    <!-- News Section -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Latest News</h2>
        <div class="row g-4">
            <!-- News Card 1 -->
            <div class="col-md-4">
                <div class="card h-100">
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
                <div class="card h-100">
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
                <div class="card h-100">
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