<?php
require_once __DIR__ . '/includes/layout.php';
render_header('cart', 'DailyBite - Cart');
?>

<section class="section">
  <div class="wrap">
    <div class="page-head">
      <h2>Your Cart</h2>
      <p class="lead">Change quantities and place your order.</p>
    </div>

    <div id="cartRoot"></div>

    <div style="margin-top:16px; color: var(--muted); font-weight:700; font-size: 12px">
      Note: Demo project checkout flow.
    </div>
  </div>
</section>

<?php render_footer(); ?>
