<?php
require_once __DIR__ . '/includes/layout.php';
render_header('deals', 'DailyBite - Deals');
?>

<section class="section">
  <div class="wrap">
    <div class="page-head">
      <h2>Deals</h2>
      <p class="lead">Special offers and bundles.</p>
    </div>

    <div class="banner" style="margin-top:6px">
      <div class="big" style="background: linear-gradient(180deg, rgba(15,23,42,.58), rgba(15,23,42,.18)), url('https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=1600&q=70');">
        <div>
          <div class="pill" style="background: rgba(255,255,255,.12); border-color: rgba(255,255,255,.22); color:#fff">TODAY ONLY</div>
          <h3>Pizza Day - Save 20%</h3>
          <p>Use Menu to add pizza items to cart and checkout.</p>
        </div>
        <a class="btn light" href="menu.php" style="text-decoration:none">Go to Menu</a>
      </div>
    </div>

    <div style="margin-top:18px">
      <h2 style="font-size:22px">Recommended</h2>
      <p class="lead">These items are popular with deals and bundles.</p>
      <div class="grid" id="dealsGrid"></div>
    </div>
  </div>
</section>

<?php render_footer(); ?>
