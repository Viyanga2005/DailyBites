<?php
require_once __DIR__ . '/includes/layout.php';
render_header('menu', 'DailyBite - Menu');
?>

<section class="section">
  <div class="wrap">
    <div class="page-head">
      <h2>Menu</h2>
      <p class="lead">Filter by category and add items to your cart.</p>
      <div class="filters">
        <button class="chip active" data-filter="All">All</button>
        <button class="chip" data-filter="Pizza">Pizza</button>
        <button class="chip" data-filter="Burgers">Burgers</button>
        <button class="chip" data-filter="Sri Lankan">Sri Lankan</button>
        <button class="chip" data-filter="Sides">Sides</button>
        <button class="chip" data-filter="Desserts">Desserts</button>
        <button class="chip" data-filter="Drinks">Drinks</button>
      </div>
    </div>

    <div class="grid" id="menuGrid"></div>
  </div>
</section>

<?php render_footer(); ?>
