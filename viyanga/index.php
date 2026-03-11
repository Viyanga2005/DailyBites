<?php
require_once __DIR__ . '/includes/layout.php';
render_header('home', 'DailyBite - Home');
?>

<section class="hero">
  <div class="wrap">
    <div class="hero-grid">
      <div class="card hero-main">
        <div>
          <h1>Business-class taste.<br>Student-class speed.</h1>
          <p>Order your favourites in seconds.</p>
          <div class="cta">
            <a class="btn light" href="menu.php">Browse Menu</a>
            <a class="btn ghost" href="deals.php">View Deals</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <h2>Featured</h2>
    <p class="lead">Popular items customers order again and again.</p>
    <div class="grid" id="featuredGrid"></div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <h2>Trending</h2>
    <p class="lead">Quick picks for lunch breaks.</p>
    <div class="grid" id="trendingGrid"></div>
  </div>
</section>

<?php render_footer(); ?>
