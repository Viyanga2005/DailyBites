(() => {
  "use strict";

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  const state = {
    products: [],
    announcements: [],
    cart: { items: [], subtotal: 0, delivery: 0, total: 0, count: 0 },
    orders: [],
    isAdmin: false,
  };

  const API = {
    async get(url) {
      const res = await fetch(url, { credentials: "same-origin" });
      return handleJson(res);
    },
    async post(url, payload) {
      const res = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload || {}),
      });
      return handleJson(res);
    },
    async upload(url, formData) {
      const res = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        body: formData,
      });
      return handleJson(res);
    },
    async del(url) {
      const res = await fetch(url, { method: "DELETE", credentials: "same-origin" });
      return handleJson(res);
    },
  };

  async function handleJson(res) {
    let data = {};
    try {
      data = await res.json();
    } catch (_e) {
      data = { ok: false, error: "Invalid server response" };
    }

    if (!res.ok || !data.ok) {
      throw new Error(data.error || "Request failed");
    }

    return data;
  }

  function escapeHtml(str) {
    return String(str ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function money(n) {
    return `Rs. ${Number(n || 0).toLocaleString("en-LK")}`;
  }

  function nowLabel(ts) {
    try {
      return new Date(ts).toLocaleString();
    } catch (_e) {
      return String(ts);
    }
  }

  let toastTimer = null;
  function toast(title, msg, ms = 2800) {
    const root = $("#toast");
    if (!root) return;

    $("#toastTitle", root).textContent = title || "Notice";
    $("#toastMsg", root).textContent = msg || "";

    root.classList.add("show");
    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => root.classList.remove("show"), ms);
  }

  function updateCartCountUI() {
    $$("#cartCount").forEach((el) => {
      el.textContent = String(state.cart.count || 0);
    });
  }

  function productCard(p) {
    const img = p.image_url ? `background-image:url('${escapeHtml(p.image_url)}')` : "";
    return `
      <div class="product">
        <div class="img" style="${img}"></div>
        <div class="body">
          <div class="tag"><span>${escapeHtml(p.category)}</span><span>${escapeHtml(p.badge || "Popular")}</span></div>
          <div style="font-weight:900">${escapeHtml(p.name)}</div>
          <div style="color:var(--muted); font-weight:600; font-size:13px">Freshly prepared • Delivery &amp; pickup</div>
          <div class="price-row">
            <div class="price">${money(p.price)}</div>
            <button class="add" data-add="${escapeHtml(p.id)}">Add</button>
          </div>
        </div>
      </div>
    `;
  }

  function renderHome() {
    const featuredGrid = $("#featuredGrid");
    const trendingGrid = $("#trendingGrid");
    if (!featuredGrid && !trendingGrid) return;

    if (featuredGrid) {
      featuredGrid.innerHTML = state.products.slice(0, 4).map(productCard).join("");
    }

    if (trendingGrid) {
      trendingGrid.innerHTML = state.products.slice(-4).map(productCard).join("");
    }
  }

  function renderMenu() {
    const grid = $("#menuGrid");
    if (!grid) return;

    const activeFilter = $(".filters .chip.active")?.getAttribute("data-filter") || "All";
    const list =
      activeFilter === "All"
        ? state.products
        : state.products.filter((p) => p.category === activeFilter);

    grid.innerHTML =
      list.map(productCard).join("") ||
      '<div class="card" style="padding:16px; grid-column: 1/-1"><strong>No items</strong></div>';
  }

  function renderDeals() {
    const grid = $("#dealsGrid");
    if (!grid) return;
    grid.innerHTML = state.products.map(productCard).join("");
  }

  function renderCart() {
    const root = $("#cartRoot");
    if (!root) return;

    const items = state.cart.items || [];

    if (!items.length) {
      root.innerHTML = `
        <div class="cart">
          <div class="row head">
            <div>Item</div><div>Price</div><div>Qty</div><div></div>
          </div>
          <div class="row">
            <div style="grid-column:1/-1;color:var(--muted);font-weight:800">Your cart is empty. Go to Menu and add items.</div>
          </div>
        </div>
      `;
      return;
    }

    root.innerHTML = `
      <div class="cartWrap">
        <div class="cart">
          <div class="row head">
            <div>Item</div><div>Price</div><div>Qty</div><div></div>
          </div>
          ${items
            .map(
              (r) => `
              <div class="row">
                <div style="display:flex; gap:12px; align-items:center">
                  <div style="width:56px; height:42px; border-radius:12px; background:#eee; background-image:url('${escapeHtml(r.image_url || "")}'); background-size:cover; background-position:center"></div>
                  <div>
                    <div style="font-weight:900">${escapeHtml(r.name)}</div>
                    <div style="font-size:12px; color:var(--muted); font-weight:700">${escapeHtml(r.category)} • ${escapeHtml(r.badge || "")}</div>
                  </div>
                </div>
                <div style="font-weight:900">${money(r.price)}</div>
                <div class="qty">
                  <button type="button" data-cart-op="dec" data-id="${escapeHtml(r.id)}">-</button>
                  <span>${Number(r.qty || 1)}</span>
                  <button type="button" data-cart-op="inc" data-id="${escapeHtml(r.id)}">+</button>
                </div>
                <button class="remove" type="button" data-cart-op="remove" data-id="${escapeHtml(r.id)}">Remove</button>
              </div>
            `
            )
            .join("")}
        </div>

        <div class="cart-summary">
          <div class="summary">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <div style="font-weight:900;font-size:16px">Summary</div>
              <div class="pill">Server Checkout</div>
            </div>
            <div class="line"><span>Subtotal</span><strong>${money(state.cart.subtotal)}</strong></div>
            <div class="line"><span>Delivery</span><strong>${money(state.cart.delivery)}</strong></div>
            <div class="line total"><span><strong>Total</strong></span><strong>${money(state.cart.total)}</strong></div>
            <div class="actions" style="margin-top:12px">
              <button class="primary" id="checkoutBtn">Place Order</button>
              <button class="chip" id="clearCartBtn" type="button">Clear Cart</button>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  function renderAdminProducts() {
    const tbody = $("#prodTbody");
    if (!tbody) return;

    tbody.innerHTML = state.products
      .map(
        (p) => `
          <tr>
            <td><strong>${escapeHtml(p.id)}</strong></td>
            <td>${escapeHtml(p.name)}</td>
            <td>${escapeHtml(p.category)}</td>
            <td>${money(p.price)}</td>
            <td>${escapeHtml(p.badge || "")}</td>
            <td>
              <button class="chip" data-edit="${escapeHtml(p.id)}">Edit</button>
              <button class="chip" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08); color: var(--hot)" data-del="${escapeHtml(p.id)}">Delete</button>
            </td>
          </tr>
        `
      )
      .join("");
  }

  function renderAdminOrders() {
    const tbody = $("#ordTbody");
    if (!tbody) return;

    if (!state.orders.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="color:var(--muted); font-weight:800; padding:14px">No orders yet.</td></tr>';
      return;
    }

    tbody.innerHTML = state.orders
      .map(
        (o) => `
          <tr>
            <td><strong>#${escapeHtml(String(o.id))}</strong></td>
            <td>${escapeHtml(nowLabel(o.created_at))}</td>
            <td>${escapeHtml(String(o.items_count || 0))} item(s)</td>
            <td>${money(o.total_amount)}</td>
            <td><span class="pill">${escapeHtml(o.status)}</span></td>
            <td style="display:flex; gap:8px; flex-wrap:wrap">
              <button class="chip" data-order-status="Pending" data-order-id="${escapeHtml(String(o.id))}">Pending</button>
              <button class="chip" data-order-status="Preparing" data-order-id="${escapeHtml(String(o.id))}">Preparing</button>
              <button class="chip" data-order-status="Delivered" data-order-id="${escapeHtml(String(o.id))}">Delivered</button>
              <button class="chip" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08); color: var(--hot)" data-order-del="${escapeHtml(String(o.id))}">Delete</button>
            </td>
          </tr>
        `
      )
      .join("");
  }

  function renderAdminAnnouncements() {
    const tbody = $("#annTbody");
    if (!tbody) return;

    if (!state.announcements.length) {
      tbody.innerHTML = '<tr><td colspan="3" style="color:var(--muted); font-weight:800; padding:14px">No notifications yet.</td></tr>';
      return;
    }

    tbody.innerHTML = state.announcements
      .map(
        (a) => `
          <tr>
            <td><strong>${escapeHtml(a.title)}</strong><div style="color:var(--muted);font-weight:600;font-size:12px;margin-top:4px">${escapeHtml(a.message)}</div></td>
            <td>${escapeHtml(nowLabel(a.created_at))}</td>
            <td><button class="chip" style="border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.08); color: var(--hot)" data-ann-del="${escapeHtml(String(a.id))}">Delete</button></td>
          </tr>
        `
      )
      .join("");
  }

  async function loadProducts() {
    const data = await API.get("api/products.php");
    state.products = data.products || [];
  }

  async function loadAnnouncements() {
    const data = await API.get("api/announcements.php");
    state.announcements = data.announcements || [];
  }

  async function loadCart() {
    const data = await API.get("api/cart.php");
    state.cart = data.cart || state.cart;
    updateCartCountUI();
  }

  async function loadOrders() {
    const data = await API.get("api/orders.php");
    state.orders = data.orders || [];
  }

  async function refreshPageData() {
    await Promise.all([loadProducts(), loadAnnouncements(), loadCart()]);
    renderHome();
    renderMenu();
    renderDeals();
    renderCart();
  }

  function bindGlobalEvents() {
    document.addEventListener("click", async (e) => {
      const addBtn = e.target.closest("[data-add]");
      if (addBtn) {
        try {
          await API.post("api/cart.php", { action: "add", id: addBtn.getAttribute("data-add"), qty: 1 });
          await loadCart();
          renderCart();
          toast("Added", "Item added to cart");
        } catch (err) {
          toast("Error", err.message);
        }
        return;
      }

      const cartOp = e.target.closest("[data-cart-op]");
      if (cartOp) {
        const id = cartOp.getAttribute("data-id");
        const op = cartOp.getAttribute("data-cart-op");
        const row = (state.cart.items || []).find((i) => i.id === id);
        const qty = Number(row?.qty || 1);

        try {
          if (op === "inc") {
            await API.post("api/cart.php", { action: "set_qty", id, qty: qty + 1 });
          } else if (op === "dec") {
            await API.post("api/cart.php", { action: "set_qty", id, qty: Math.max(1, qty - 1) });
          } else if (op === "remove") {
            await API.post("api/cart.php", { action: "remove", id });
          }
          await loadCart();
          renderCart();
        } catch (err) {
          toast("Error", err.message);
        }
        return;
      }

      if (e.target.id === "clearCartBtn") {
        try {
          await API.post("api/cart.php", { action: "clear" });
          await loadCart();
          renderCart();
          toast("Cart cleared", "Your cart is now empty");
        } catch (err) {
          toast("Error", err.message);
        }
        return;
      }

      if (e.target.id === "checkoutBtn") {
        try {
          const data = await API.post("api/cart.php", { action: "checkout" });
          await loadCart();
          renderCart();
          toast("Order placed", `Order #${data.order_id}`);
        } catch (err) {
          toast("Checkout failed", err.message);
        }
      }
    });

    $$(".filters [data-filter]").forEach((btn) => {
      btn.addEventListener("click", () => {
        $$(".filters .chip").forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        renderMenu();
      });
    });
  }

  function setupMobileNav() {
    const hamburger = $("#hamburger");
    if (!hamburger || $(".mobile-drawer")) return;

    const nav = $(".nav");
    const drawer = document.createElement("div");
    drawer.className = "mobile-drawer";
    drawer.style.display = "none";
    drawer.innerHTML = `
      <div class="drawer-panel" style="position:absolute; right:16px; top:16px; width:min(360px, calc(100% - 32px)); background:#fff; border:1px solid rgba(2,6,23,.10); border-radius:18px; padding:12px; box-shadow: 0 20px 40px rgba(2,6,23,.25);">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding:6px 6px 10px">
          <strong style="font-size:14px">Menu</strong>
          <button id="drawerClose" class="icon-btn" aria-label="Close" title="Close" style="width:40px;height:40px">X</button>
        </div>
        <div class="drawer-links" style="display:flex; flex-direction:column; gap:8px; padding:6px">${nav ? nav.innerHTML : ""}</div>
      </div>
    `;

    function show() {
      drawer.style.position = "fixed";
      drawer.style.inset = "0";
      drawer.style.background = "rgba(2,6,23,.45)";
      drawer.style.backdropFilter = "blur(3px)";
      drawer.style.zIndex = "999";
      drawer.style.display = "block";
      document.body.style.overflow = "hidden";
    }

    function hide() {
      drawer.style.display = "none";
      document.body.style.overflow = "";
    }

    drawer.addEventListener("click", (e) => {
      if (e.target === drawer) hide();
    });

    document.body.appendChild(drawer);
    hamburger.addEventListener("click", show);
    $("#drawerClose", drawer)?.addEventListener("click", hide);
    drawer.querySelectorAll("a").forEach((a) => a.addEventListener("click", hide));
  }

  async function setupAdmin() {
    const page = document.body?.getAttribute("data-page");
    if (page !== "admin") return;

    const loginView = $("#adminLoginView");
    const appView = $("#adminAppView");

    const setAdminView = (loggedIn) => {
      state.isAdmin = loggedIn;
      if (loginView) loginView.style.display = loggedIn ? "none" : "block";
      if (appView) appView.style.display = loggedIn ? "block" : "none";
    };

    try {
      const auth = await API.get("api/auth.php");
      setAdminView(Boolean(auth.authenticated));
    } catch (_e) {
      setAdminView(false);
    }

    $("#adminLoginForm")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      try {
        await API.post("api/auth.php", {
          action: "login",
          username: $("#adminUser")?.value || "",
          password: $("#adminPass")?.value || "",
        });
        setAdminView(true);
        await refreshAdminData();
        toast("Welcome", "Admin session started");
      } catch (err) {
        toast("Login failed", err.message);
      }
    });

    $("#adminLogoutBtn")?.addEventListener("click", async () => {
      try {
        await API.post("api/auth.php", { action: "logout" });
        setAdminView(false);
        toast("Logged out", "Admin session ended");
      } catch (err) {
        toast("Error", err.message);
      }
    });

    $$("[data-admin-tab]").forEach((btn) => {
      btn.addEventListener("click", () => {
        $$("[data-admin-tab]").forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        const tab = btn.getAttribute("data-admin-tab");
        $$("[data-admin-panel]").forEach((panel) => {
          panel.style.display = panel.getAttribute("data-admin-panel") === tab ? "block" : "none";
        });
      });
    });

    $("#p_img")?.addEventListener("change", (e) => {
      const file = e.target.files?.[0];
      if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = (ev) => {
          $("#previewImg").src = ev.target.result;
          $("#imgPreview").style.display = "block";
          $("#imgPreviewLabel").style.display = "inline-block";
        };
        reader.readAsDataURL(file);
      }
    });

    $("#clearProductFormBtn")?.addEventListener("click", () => {
      $("#productForm")?.reset();
      $("#p_id").value = "";
      $("#imgPreview").style.display = "none";
      $("#imgPreviewLabel").style.display = "none";
    });

    $("#productForm")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      try {
        let imageUrl = $("#p_img")?.value || "";
        const fileInput = $("#p_img");
        const file = fileInput?.files?.[0];

        // Upload image if a new file is selected
        if (file) {
          const formData = new FormData();
          formData.append("image", file);
          const uploadResult = await API.upload("api/upload-image.php", formData);
          imageUrl = uploadResult.image_url;
        }

        await API.post("api/products.php", {
          id: $("#p_id")?.value || "",
          name: $("#p_name")?.value || "",
          category: $("#p_cat")?.value || "Other",
          price: Number($("#p_price")?.value || 0),
          badge: $("#p_badge")?.value || "Popular",
          image_url: imageUrl,
        });

        $("#productForm")?.reset();
        $("#p_id").value = "";
        $("#imgPreview").style.display = "none";
        $("#imgPreviewLabel").style.display = "none";
        await loadProducts();
        renderAdminProducts();
        renderHome();
        renderMenu();
        renderDeals();
        toast("Saved", "Product updated");
      } catch (err) {
        toast("Save failed", err.message);
      }
    });

    $("#announcementForm")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      try {
        await API.post("api/announcements.php", {
          title: $("#a_title")?.value || "",
          message: $("#a_msg")?.value || "",
        });

        $("#announcementForm")?.reset();
        await loadAnnouncements();
        renderAdminAnnouncements();
        toast("Sent", "Notification created");
      } catch (err) {
        toast("Failed", err.message);
      }
    });

    document.addEventListener("click", async (e) => {
      const editBtn = e.target.closest("[data-edit]");
      if (editBtn) {
        const id = editBtn.getAttribute("data-edit");
        const p = state.products.find((x) => x.id === id);
        if (!p) return;

        $("#p_id").value = p.id;
        $("#p_name").value = p.name;
        $("#p_cat").value = p.category;
        $("#p_price").value = p.price;
        $("#p_badge").value = p.badge || "";
        $("#p_img").value = "";
        $("#imgPreview").style.display = "none";
        $("#imgPreviewLabel").style.display = "none";
        return;
      }

      const delBtn = e.target.closest("[data-del]");
      if (delBtn) {
        try {
          const id = delBtn.getAttribute("data-del");
          await API.del(`api/products.php?id=${encodeURIComponent(id)}`);
          await loadProducts();
          renderAdminProducts();
          renderHome();
          renderMenu();
          renderDeals();
          toast("Deleted", "Product removed");
        } catch (err) {
          toast("Delete failed", err.message);
        }
        return;
      }

      const orderStatus = e.target.closest("[data-order-status]");
      if (orderStatus) {
        try {
          await API.post("api/orders.php", {
            action: "status",
            id: Number(orderStatus.getAttribute("data-order-id")),
            status: orderStatus.getAttribute("data-order-status"),
          });
          await loadOrders();
          renderAdminOrders();
          toast("Updated", "Order status changed");
        } catch (err) {
          toast("Update failed", err.message);
        }
        return;
      }

      const orderDel = e.target.closest("[data-order-del]");
      if (orderDel) {
        try {
          await API.post("api/orders.php", {
            action: "delete",
            id: Number(orderDel.getAttribute("data-order-del")),
          });
          await loadOrders();
          renderAdminOrders();
          toast("Deleted", "Order removed");
        } catch (err) {
          toast("Delete failed", err.message);
        }
        return;
      }

      const annDel = e.target.closest("[data-ann-del]");
      if (annDel) {
        try {
          const id = annDel.getAttribute("data-ann-del");
          await API.del(`api/announcements.php?id=${encodeURIComponent(id)}`);
          await loadAnnouncements();
          renderAdminAnnouncements();
          toast("Deleted", "Notification removed");
        } catch (err) {
          toast("Delete failed", err.message);
        }
      }
    });

    if (state.isAdmin) {
      await refreshAdminData();
    }
  }

  async function refreshAdminData() {
    await Promise.all([loadProducts(), loadAnnouncements(), loadOrders()]);
    renderAdminProducts();
    renderAdminOrders();
    renderAdminAnnouncements();
  }

  async function init() {
    setupMobileNav();
    bindGlobalEvents();

    try {
      await refreshPageData();
    } catch (err) {
      toast("Server error", err.message);
    }

    await setupAdmin();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
