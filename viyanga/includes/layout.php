<?php

function render_header(string $page, string $title): void
{
    $nav = [
        'home' => ['label' => 'Home', 'href' => 'index.php'],
        'menu' => ['label' => 'Menu', 'href' => 'menu.php'],
        'deals' => ['label' => 'Deals', 'href' => 'deals.php'],
        'cart' => ['label' => 'Cart', 'href' => 'cart.php'],
        'admin' => ['label' => 'Admin', 'href' => 'admin.php'],
    ];

    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="icon" href="images/logo.svg">
  <link rel="stylesheet" href="style.css">
</head>
<body data-page="<?= htmlspecialchars($page) ?>">
<div class="topbar"><div class="wrap"><div class="row"></div></div></div>
<header class="site">
  <div class="wrap">
    <div class="row">
      <a class="brand" href="index.php">
        <img src="images/logo.svg" alt="DailyBite logo">
        <div>
          <strong>DailyBite</strong>
          <span>Food selling website</span>
        </div>
      </a>

      <nav class="nav" aria-label="Primary">
        <?php foreach ($nav as $key => $item): ?>
          <a class="<?= $page === $key ? 'active' : '' ?>" href="<?= $item['href'] ?>"><?= $item['label'] ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="actions">
        <a class="icon-btn" href="cart.php" title="Cart" aria-label="Cart">
          Cart <span class="count" id="cartCount">0</span>
        </a>
        <button class="icon-btn hamburger" id="hamburger" title="Menu" aria-label="Open menu">Menu</button>
        <a class="primary" href="menu.php">Order Now</a>
      </div>
    </div>
  </div>
</header>
<?php
}

function render_footer(): void
{
    ?>
<footer>
  <div class="wrap">
    <div class="cols">
      <div>
        <div style="display:flex;align-items:center;gap:10px">
          <img src="images/logo.svg" alt="" style="height:46px">
          <div>
            <div style="font-weight:900; font-size:16px">DailyBite</div>
            <div style="color: rgba(255,255,255,.72); font-weight:700; font-size:12px">Food • Fast • Fresh</div>
          </div>
        </div>
        <div class="tiny">&copy; 2026 DailyBite PVT &amp; LTD</div>
      </div>
      <div>
        <div style="font-weight:900; margin-bottom:10px">Quick Links</div>
        <div style="display:flex; flex-direction:column; gap:8px">
          <a href="menu.php">Menu</a>
          <a href="deals.php">Deals</a>
          <a href="cart.php">Cart</a>
          <a href="admin.php">Admin</a>
        </div>
      </div>
      <div>
        <div style="font-weight:900; margin-bottom:10px">Contact</div>
        <div style="display:flex; flex-direction:column; gap:8px">
          <a href="tel:0760000000">076 000 0000</a>
          <a href="#">support@dailybite.demo</a>
          <a href="#">No. 77, Food Street, Colombo</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<div class="toast" id="toast" role="status" aria-live="polite">
  <div class="t" id="toastTitle">Update</div>
  <div class="m" id="toastMsg">...</div>
</div>

<script src="app.js"></script>
</body>
</html>
<?php
}
