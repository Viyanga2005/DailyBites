<?php
require_once __DIR__ . '/includes/layout.php';
render_header('admin', 'DailyBite - Admin');
?>

<section class="section">
  <div class="wrap">
    <div class="page-head">
      <h2>Admin Panel</h2>
      <p class="lead">Manage products, orders, and notifications.</p>
    </div>

    <div class="card" id="adminLoginView" style="padding: 18px; max-width: 620px; display: none;">
      <div class="pill">Login</div>
      <h3 style="margin:10px 0 0">Admin Sign In</h3>
      <p style="margin:8px 0 0; color: var(--muted); font-weight:700">
        Default: username <strong>admin</strong> and password <strong>admin123</strong>
      </p>

      <form id="adminLoginForm" style="margin-top:14px">
        <div class="form-grid">
          <div>
            <label style="font-weight:900; font-size: 12px">Username</label>
            <input class="input" id="adminUser" placeholder="admin" value="admin" autocomplete="username">
          </div>
          <div>
            <label style="font-weight:900; font-size: 12px">Password</label>
            <input class="input" id="adminPass" placeholder="admin123" value="admin123" type="password" autocomplete="current-password">
          </div>
        </div>
        <div style="margin-top:12px; display:flex; gap:10px; align-items:center; flex-wrap:wrap">
          <button class="primary" type="submit">Login</button>
        </div>
      </form>
    </div>

    <div id="adminAppView" style="display: none;">
      <div class="admin-shell" id="adminShell">
        <div class="sidebar">
          <div class="user">
            <strong>Logged in as Admin</strong>
            <div style="color: var(--muted); font-weight:700; font-size: 12px">DailyBite Dashboard</div>
          </div>
          <nav>
            <button data-admin-tab="products" class="active">Products</button>
            <button data-admin-tab="orders">Orders</button>
            <button data-admin-tab="notifications">Notifications</button>
            <button id="adminLogoutBtn" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08); color: var(--hot)">Logout</button>
          </nav>
        </div>

        <div class="panel">
          <div data-admin-panel="products" style="display: block;">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap">
              <h3 style="margin:0">Products</h3>
              <div class="pill">Create / Edit / Delete</div>
            </div>

            <form id="productForm" style="margin-top:12px">
              <input id="p_id" type="hidden">
              <div class="form-grid">
                <div>
                  <label style="font-weight:900; font-size: 12px">Name</label>
                  <input class="input" id="p_name" placeholder="e.g., Spicy Chicken Pizza">
                </div>
                <div>
                  <label style="font-weight:900; font-size: 12px">Category</label>
                  <input class="input" id="p_cat" placeholder="Pizza / Burgers / Sri Lankan">
                </div>
                <div>
                  <label style="font-weight:900; font-size: 12px">Price (LKR)</label>
                  <input class="input" id="p_price" type="number" placeholder="1990">
                </div>
                <div>
                  <label style="font-weight:900; font-size: 12px">Badge</label>
                  <input class="input" id="p_badge" placeholder="Best Seller / New / Hot Deal">
                </div>
              </div>
              <div style="margin-top:10px">
                <label style="font-weight:900; font-size: 12px">Product Image</label>
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center">
                  <input class="input" id="p_img" type="file" accept="image/*" style="flex:1; min-width:200px">
                  <span id="imgPreviewLabel" style="color:var(--muted); font-size:12px; display:none">✓ Image selected</span>
                </div>
                <div id="imgPreview" style="margin-top:8px; max-width:150px; border-radius:8px; overflow:hidden; display:none">
                  <img id="previewImg" style="width:100%; height:auto; object-fit:cover">
                </div>
              </div>

              <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap; align-items:center">
                <button class="primary" type="submit">Save Product</button>
                <button class="chip" type="button" id="clearProductFormBtn">Clear</button>
              </div>
            </form>

            <div style="margin-top:14px; overflow:auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Badge</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody id="prodTbody"></tbody>
              </table>
            </div>
          </div>

          <div data-admin-panel="orders" style="display:none">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap">
              <h3 style="margin:0">Orders</h3>
              <div class="pill">Placed from Cart page</div>
            </div>
            <div style="margin-top:14px; overflow:auto">
              <table class="table">
                <thead>
                  <tr>
                    <th>Order ID</th><th>Date</th><th>Items</th><th>Total</th><th>Status</th><th>Actions</th>
                  </tr>
                </thead>
                <tbody id="ordTbody"></tbody>
              </table>
            </div>
          </div>

          <div data-admin-panel="notifications" style="display:none">
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap">
              <h3 style="margin:0">Notifications</h3>
              <div class="pill">Shows as toast</div>
            </div>

            <form id="announcementForm" style="margin-top:12px">
              <div class="form-grid">
                <div>
                  <label style="font-weight:900; font-size: 12px">Title</label>
                  <input class="input" id="a_title" placeholder="e.g., Flash Sale!">
                </div>
                <div>
                  <label style="font-weight:900; font-size: 12px">Message</label>
                  <input class="input" id="a_msg" placeholder="e.g., 10% OFF on Burgers until 9 PM">
                </div>
              </div>
              <div style="margin-top:12px">
                <button class="primary" type="submit">Send Notification</button>
              </div>
            </form>

            <div style="margin-top:14px; overflow:auto">
              <table class="table">
                <thead>
                  <tr><th>Notification</th><th>Date</th><th></th></tr>
                </thead>
                <tbody id="annTbody"></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<?php render_footer(); ?>
