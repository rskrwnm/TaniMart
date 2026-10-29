<?php
  session_start();
  if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'farmer') {
    $loginPage = ($_SESSION['role'] ?? '') === 'user' ? '../html/index.html' : '../html/farmer-login.html';
    header('Location: ' . $loginPage);
    exit();
  }

  require_once('cnkt.php');

  $deliveryColumn = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_status'");
  if (!$deliveryColumn->fetch(PDO::FETCH_ASSOC)) {
    $conn->exec("ALTER TABLE orders ADD COLUMN delivery_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
  }

  if (empty($_SESSION['product_form_token'])) {
    $_SESSION['product_form_token'] = bin2hex(random_bytes(32));
  }
  if (empty($_SESSION['delivery_form_token'])) {
    $_SESSION['delivery_form_token'] = bin2hex(random_bytes(32));
  }

  $productMessage = $_SESSION['product_message'] ?? '';
  $productMessageType = $_SESSION['product_message_type'] ?? 'success';
  unset($_SESSION['product_message'], $_SESSION['product_message_type']);
  $deliveryMessage = $_SESSION['delivery_message'] ?? '';
  $deliveryMessageType = $_SESSION['delivery_message_type'] ?? 'success';
  unset($_SESSION['delivery_message'], $_SESSION['delivery_message_type']);

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_delivery'])) {
    $message = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
    $messageType = 'error';

    if (hash_equals($_SESSION['delivery_form_token'], $_POST['delivery_form_token'] ?? '')) {
      $orderId = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
      $nextStatus = $_POST['next_status'] ?? '';
      $validTransitions = [
        'in_delivery' => 'pending',
        'delivered' => 'in_delivery',
      ];

      if ($orderId === false || $orderId < 1 || !isset($validTransitions[$nextStatus])) {
        $message = 'Pesanan atau status pengiriman tidak valid.';
      } else {
        $stmt = $conn->prepare('UPDATE orders SET delivery_status = ? WHERE id = ? AND delivery_status = ?');
        $stmt->execute([$nextStatus, $orderId, $validTransitions[$nextStatus]]);

        if ($stmt->rowCount() === 1) {
          $message = $nextStatus === 'delivered'
            ? 'Pesanan berhasil ditandai sudah terkirim.'
            : 'Pengiriman pesanan berhasil dimulai.';
          $messageType = 'success';
        } else {
          $message = 'Status pesanan tidak berubah. Muat ulang halaman dan periksa status terbaru.';
        }
      }
    }

    $_SESSION['delivery_message'] = $message;
    $_SESSION['delivery_message_type'] = $messageType;
    header('Location: home-farmer.php#delivery');
    exit();
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manage_product'])) {
    $message = '';
    $messageType = 'error';

    if (!hash_equals($_SESSION['product_form_token'], $_POST['product_form_token'] ?? '')) {
      $message = 'Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.';
    } else {
      $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
      $action = $_POST['manage_product'];

      if ($productId === false || $productId < 1) {
        $message = 'Produk yang dipilih tidak valid.';
      } elseif ($action === 'add_stock') {
        $stockToAdd = filter_var($_POST['stock_to_add'] ?? null, FILTER_VALIDATE_INT);
        if ($stockToAdd === false || $stockToAdd < 1) {
          $message = 'Jumlah stok tambahan harus berupa bilangan bulat minimal 1.';
        } else {
          $stmt = $conn->prepare('UPDATE product SET product_qty = COALESCE(product_qty, 0) + ? WHERE id = ?');
          $stmt->execute([$stockToAdd, $productId]);
          if ($stmt->rowCount() > 0) {
            $message = 'Stok produk berhasil ditambahkan.';
            $messageType = 'success';
          } else {
            $stmt = $conn->prepare('SELECT id FROM product WHERE id = ?');
            $stmt->execute([$productId]);
            $productExists = $stmt->fetchColumn() !== false;
            $message = $productExists ? 'Stok produk tidak berubah.' : 'Produk tidak ditemukan.';
            $messageType = $productExists ? 'success' : 'error';
          }
        }
      } elseif ($action === 'delete') {
        $stmt = $conn->prepare('SELECT product_image FROM product WHERE id = ?');
        $stmt->execute([$productId]);
        $imagePath = $stmt->fetchColumn();

        if ($imagePath === false) {
          $message = 'Produk tidak ditemukan.';
        } else {
          $stmt = $conn->prepare('DELETE FROM product WHERE id = ?');
          $stmt->execute([$productId]);
          $message = 'Produk berhasil dihapus.';
          $messageType = 'success';

          if (is_string($imagePath) && str_starts_with($imagePath, '../image/uploads/')) {
            $uploadedImage = __DIR__ . '/../image/uploads/' . basename($imagePath);
            if (is_file($uploadedImage) && !unlink($uploadedImage)) {
              error_log('Unable to remove deleted product image: ' . $uploadedImage);
            }
          }
        }
      } else {
        $message = 'Aksi produk tidak dikenal.';
      }
    }

    $_SESSION['product_message'] = $message;
    $_SESSION['product_message_type'] = $messageType;
    header('Location: home-farmer.php#products');
    exit();
  }

  $stmt = $conn->prepare("SELECT * FROM product ORDER BY id DESC");
  $stmt->execute();
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt = $conn->prepare("SELECT * FROM orders");
  $stmt->execute();
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $payLabels = [
    'cod' => 'Cash on delivery',
    'netbanking' => 'Net banking',
    'cards' => 'Card',
  ];
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Petani | TaniMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Nunito+Sans:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.6.3/css/all.css"
    />
    <style>
      :root {
        --forest: #163a24;
        --leaf: #2e7d45;
        --leaf-dark: #22633a;
        --marigold: #f4b72f;
        --paper: #f2f6ec;
        --ink: #1b2a1f;
        --muted: #667467;
        --line: #d5dfcb;
      }
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { font-family: "Nunito Sans", system-ui, sans-serif; color: var(--ink); background: var(--paper); min-height: 100vh; }
      h1, h2 { font-family: "Bricolage Grotesque", "Nunito Sans", sans-serif; letter-spacing: -0.02em; line-height: 1.1; }
      button:focus-visible { outline: 3px solid var(--marigold); outline-offset: 2px; }
      .wrap { width: min(1180px, 100% - 40px); margin-inline: auto; }

      .sidebar { position: fixed; inset: 0 auto 0 0; z-index: 20; width: 250px; padding: 28px 18px; background: var(--forest); color: #fff; }
      .sidebar-brand { display: flex; align-items: center; gap: 12px; margin: 0 8px 34px; font-family: "Bricolage Grotesque", sans-serif; font-size: 22px; font-weight: 800; }
      .sidebar-brand .brand-mark { width: 42px; height: 42px; font-size: 18px; }
      .sidebar nav { display: grid; gap: 8px; }
      .sidebar nav a { display: flex; align-items: center; gap: 12px; padding: 13px 14px; border-radius: 12px; color: #e4efd8; text-decoration: none; font-weight: 700; transition: background .2s, color .2s; }
      .sidebar nav a:hover, .sidebar nav a:focus-visible, .sidebar nav a[aria-current="location"] { background: var(--leaf); color: #fff; }
      .sidebar nav a:focus-visible { outline: 3px solid var(--marigold); outline-offset: 2px; }
      .sidebar nav i { width: 20px; text-align: center; }

      /* Header */
      .top { position: relative; overflow: hidden; margin-left: 250px; background: var(--forest); color: #fff; padding: 44px 0 56px; }
      .top::before { content: ""; position: absolute; width: 240px; height: 240px; border-radius: 50%; background: var(--marigold); right: 6%; top: -120px; }
      .top::after { content: ""; position: absolute; left: -10%; right: -10%; bottom: -110px; height: 150px; border-radius: 50% 50% 0 0; background: var(--leaf); box-shadow: 0 -26px 0 -10px #3b9455; }
      .top .wrap { position: relative; z-index: 2; display: flex; align-items: center; gap: 16px; }
      .brand-mark { flex: none; width: 52px; height: 52px; border-radius: 16px 16px 16px 5px; background: var(--leaf); border: 2px solid rgba(255, 255, 255, .35); display: grid; place-items: center; font-size: 22px; }
      .top h1 { font-size: clamp(26px, 4vw, 38px); font-weight: 800; }
      .top p { color: #e4efd8; margin-top: 6px; }

      /* Tabel */
      main { margin-left: 250px; padding: 28px 0 48px; }
      main.wrap { width: min(1180px, calc(100% - 290px)); margin-left: 270px; margin-right: 20px; }
      .panel { background: #fff; border-radius: 20px; padding: 8px 8px 12px; box-shadow: 0 12px 28px -18px rgba(22, 58, 36, .5); }
      .form-message { margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; font-weight: 700; }
      .form-message.success { background: #e3ecd6; color: var(--forest); }
      .form-message.error { background: #fde8e5; color: #8c241a; }
      .heading { display: flex; align-items: center; gap: 16px; }
      .top-action { display: inline-flex; align-items: center; gap: 8px; padding: 11px 16px; border-radius: 12px; background: var(--marigold); color: var(--forest); text-decoration: none; font-weight: 800; white-space: nowrap; }
      .top-action:hover { background: #ffc94d; }
      .products-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
      .section-heading { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px 12px; }
      .section-heading h2 { color: var(--forest); font-size: 22px; }
      .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; padding: 8px; }
      .product-card { overflow: hidden; border: 1px solid var(--line); border-radius: 14px; background: #fff; }
      .product-card img { display: block; width: 100%; height: 150px; object-fit: cover; background: var(--paper); }
      .product-info { padding: 12px; }
      .product-info h3 { margin-bottom: 6px; color: var(--forest); font-size: 17px; }
      .product-price { color: var(--leaf-dark); font-weight: 800; }
      .product-stock { margin-top: 3px; color: var(--muted); font-size: 14px; }
      .product-actions { display: grid; gap: 8px; margin-top: 14px; }
      .stock-form { display: flex; gap: 8px; }
      .stock-form input { width: 76px; min-width: 0; padding: 8px; border: 1px solid var(--line); border-radius: 9px; font: inherit; }
      .stock-form button, .delete-form button { min-height: 38px; padding: 8px 10px; border: 0; border-radius: 9px; font: inherit; font-size: 13px; font-weight: 800; cursor: pointer; }
      .stock-form button { flex: 1; background: var(--leaf); color: #fff; }
      .stock-form button:hover { background: var(--leaf-dark); }
      .delete-form button { width: 100%; background: #fde8e5; color: #8c241a; }
      .delete-form button:hover { background: #f8d3cd; }
      .product-notice { margin: 0 8px 12px; }
      #delivery { margin-top: 24px; }
      .count { display: flex; justify-content: space-between; align-items: baseline; padding: 14px 16px 6px; color: var(--muted); font-size: 14px; }
      .count strong { font-family: "Bricolage Grotesque", sans-serif; font-size: 20px; color: var(--forest); }
      .scroll { overflow-x: auto; }
      table { width: 100%; min-width: 900px; border-collapse: collapse; }
      th { text-align: left; padding: 12px 14px; font-size: 13px; font-weight: 700; color: var(--muted); border-bottom: 2px solid var(--line); white-space: nowrap; }
      td { padding: 14px; border-bottom: 1px solid var(--line); vertical-align: top; font-size: 15px; }
      tbody tr { transition: background .2s; }
      tbody tr:hover { background: #f6faf0; }
      tbody tr:last-child td { border-bottom: 0; }
      .no { font-family: "Bricolage Grotesque", sans-serif; font-weight: 800; color: var(--forest); }
      .name { font-weight: 700; }
      .muted { color: var(--muted); font-size: 14px; }
      .addr { min-width: 200px; }
      .chip { display: inline-block; margin: 0 4px 4px 0; padding: 3px 10px; border-radius: 999px; background: #e3ecd6; color: var(--forest); font-size: 13px; font-weight: 600; white-space: nowrap; }
      .pay { display: inline-block; padding: 4px 12px; border-radius: 999px; background: #fdf2d3; color: #7a5600; font-size: 13px; font-weight: 700; white-space: nowrap; }
      .amount { font-family: "Bricolage Grotesque", sans-serif; font-weight: 800; color: var(--leaf-dark); white-space: nowrap; }
      .btn-deliver { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 0; border-radius: 12px; background: var(--marigold); color: var(--forest); font: inherit; font-weight: 800; font-size: 14px; cursor: pointer; white-space: nowrap; box-shadow: 0 8px 16px -10px rgba(0, 0, 0, .5); transition: background .2s, transform .15s; }
      .btn-deliver:hover { background: #ffc94d; }
      .btn-deliver:active { transform: scale(.97); }
      .delivery-status { display: inline-block; padding: 5px 10px; border-radius: 999px; background: #f0f2ed; color: var(--muted); font-size: 13px; font-weight: 800; white-space: nowrap; }
      .delivery-status.in_delivery { background: #fdf2d3; color: #7a5600; }
      .delivery-status.delivered { background: #e3ecd6; color: var(--forest); }
      .delivery-form { display: grid; gap: 8px; }

      .empty { text-align: center; padding: 56px 16px; color: var(--muted); }
      .empty i { font-size: 48px; color: #9bb88a; margin-bottom: 12px; display: block; }

      /* Notifikasi */
      #toast { position: fixed; left: 50%; bottom: 28px; transform: translate(-50%, 24px); max-width: calc(100% - 32px); padding: 14px 20px; border-radius: 14px; background: var(--forest); color: #fff; font-weight: 600; box-shadow: 0 16px 32px -12px rgba(0, 0, 0, .5); opacity: 0; pointer-events: none; transition: opacity .3s, transform .3s; z-index: 10; }
      #toast.show { opacity: 1; transform: translate(-50%, 0); }
      #toast i { color: var(--marigold); margin-right: 8px; }

      @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
      @media (max-width: 760px) {
        .sidebar { position: static; width: 100%; padding: 12px 16px; }
        .sidebar-brand { margin: 0 0 10px; font-size: 19px; }
        .sidebar-brand .brand-mark { width: 36px; height: 36px; }
        .sidebar nav { display: flex; flex-wrap: wrap; gap: 6px; }
        .sidebar nav a { flex: 1 1 auto; justify-content: center; padding: 10px; font-size: 14px; }
        .top, main { margin-left: 0; }
        main.wrap { width: min(1180px, calc(100% - 40px)); margin-inline: auto; }
        .top { padding: 28px 0 42px; }
        .top .wrap { align-items: flex-start; gap: 18px; }
      }
      @media (max-width: 420px) {
        .product-grid { grid-template-columns: 1fr; }
        .stock-form { flex-wrap: wrap; }
        .stock-form input { flex: 1; }
      }
    </style>
  </head>
  <body>
    <aside class="sidebar" aria-label="Navigasi dashboard petani">
      <div class="sidebar-brand">
        <span class="brand-mark"><i class="fas fa-tractor"></i></span>
        <span>TaniMart</span>
      </div>
      <nav>
        <a href="#products"><i class="fas fa-box-open"></i>Produk</a>
        <a href="add-product.php"><i class="fas fa-plus-circle"></i>Tambah Produk</a>
        <a href="#delivery"><i class="fas fa-truck"></i>Pengiriman</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Keluar</a>
      </nav>
    </aside>

    <header class="top">
      <div class="wrap">
        <div class="heading">
          <div class="brand-mark"><i class="fas fa-tractor"></i></div>
          <div>
            <h1>Halo, Petani</h1>
            <p>Kelola produk dan pesanan Anda.</p>
          </div>
        </div>
      </div>
    </header>

    <main class="wrap">
      <div class="products-toolbar">
        <a class="top-action" href="add-product.php"><i class="fas fa-plus"></i>Tambah Produk</a>
      </div>
      <section class="panel" id="products" aria-labelledby="products-title">
        <div class="section-heading">
          <h2 id="products-title">Produk Anda</h2>
          <span class="muted"><?= count($products) ?> produk</span>
        </div>
        <?php if ($productMessage !== ''): ?>
        <div class="form-message product-notice <?= htmlspecialchars($productMessageType, ENT_QUOTES, 'UTF-8') ?>" role="status">
          <?= htmlspecialchars($productMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        <?php if (count($products) === 0): ?>
        <div class="empty">
          <i class="fas fa-seedling"></i>
          <h2>Belum ada produk</h2>
          <p>Tambahkan produk pertama Anda agar tampil di katalog.</p>
        </div>
        <?php else: ?>
        <div class="product-grid">
          <?php foreach ($products as $product): ?>
          <article class="product-card">
            <img src="<?= htmlspecialchars($product['product_image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
            <div class="product-info">
              <h3><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p class="product-price">Rp<?= number_format((float) $product['product_price'], 0, ',', '.') ?></p>
              <p class="product-stock">Stok: <?= htmlspecialchars($product['product_qty'], ENT_QUOTES, 'UTF-8') ?></p>
              <div class="product-actions">
                <form class="stock-form" method="post">
                  <input type="hidden" name="product_form_token" value="<?= htmlspecialchars($_SESSION['product_form_token'], ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                  <input type="number" name="stock_to_add" min="1" step="1" value="1" aria-label="Jumlah stok tambahan untuk <?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                  <button type="submit" name="manage_product" value="add_stock"><i class="fas fa-plus"></i> Tambah Stok</button>
                </form>
                <form class="delete-form" method="post" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                  <input type="hidden" name="product_form_token" value="<?= htmlspecialchars($_SESSION['product_form_token'], ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                  <button type="submit" name="manage_product" value="delete"><i class="fas fa-trash"></i> Hapus Produk</button>
                </form>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </section>

      <div class="panel" id="delivery">
        <?php if ($deliveryMessage !== ''): ?>
        <div class="form-message product-notice <?= htmlspecialchars($deliveryMessageType, ENT_QUOTES, 'UTF-8') ?>" role="status">
          <?= htmlspecialchars($deliveryMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        <div class="count">
          <span>Pesanan masuk</span>
          <strong><?= count($orders) ?></strong>
        </div>

        <?php if (count($orders) === 0): ?>
        <div class="empty">
          <i class="fas fa-box-open"></i>
          <h2>No orders yet</h2>
          <p>New orders will show up here.</p>
        </div>
        <?php else: ?>
        <div class="scroll">
          <table>
            <thead>
              <tr>
                <th>Order no.</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Products</th>
                <th>Payment</th>
                <th>Amount paid</th>
                <th>Status / Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $row): ?>
              <tr>
                <td class="no">#<?= htmlspecialchars($row['id']) ?></td>
                <td>
                  <div class="name"><?= htmlspecialchars($row['name']) ?></div>
                  <div class="muted"><?= htmlspecialchars($row['email']) ?></div>
                </td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td class="addr"><?= nl2br(htmlspecialchars($row['address'])) ?></td>
                <td>
                  <?php foreach (explode(', ', $row['products']) as $product): ?>
                  <span class="chip"><?= htmlspecialchars($product) ?></span>
                  <?php endforeach; ?>
                </td>
                <td>
                  <span class="pay"><?= htmlspecialchars(isset($payLabels[$row['pmode']]) ? $payLabels[$row['pmode']] : $row['pmode']) ?></span>
                </td>
                <td class="amount">Rp<?= number_format($row['amount_paid'], 0, ',', '.') ?></td>
                <td>
                  <?php
                    $deliveryStatus = $row['delivery_status'] ?? 'pending';
                    $deliveryLabels = [
                      'pending' => 'Menunggu',
                      'in_delivery' => 'Dalam Pengiriman',
                      'delivered' => 'Terkirim',
                    ];
                  ?>
                  <div class="delivery-form">
                    <span class="delivery-status <?= htmlspecialchars($deliveryStatus, ENT_QUOTES, 'UTF-8') ?>">
                      <?= htmlspecialchars($deliveryLabels[$deliveryStatus] ?? 'Status tidak dikenal', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php if ($deliveryStatus === 'pending' || $deliveryStatus === 'in_delivery'): ?>
                    <form method="post">
                      <input type="hidden" name="delivery_form_token" value="<?= htmlspecialchars($_SESSION['delivery_form_token'], ENT_QUOTES, 'UTF-8') ?>">
                      <input type="hidden" name="order_id" value="<?= (int) $row['id'] ?>">
                      <input type="hidden" name="next_status" value="<?= $deliveryStatus === 'pending' ? 'in_delivery' : 'delivered' ?>">
                      <button class="btn-deliver" type="submit" name="update_delivery">
                        <i class="fas <?= $deliveryStatus === 'pending' ? 'fa-truck' : 'fa-check' ?>"></i>
                        <?= $deliveryStatus === 'pending' ? 'Mulai Pengiriman' : 'Tandai Terkirim' ?>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </main>

    <script>
      const sectionLinks = document.querySelectorAll('.sidebar nav a[href^="#"]');
      function updateActiveSection() {
        const activeHash = window.location.hash || "#products";
        sectionLinks.forEach((link) => {
          if (link.getAttribute("href") === activeHash) {
            link.setAttribute("aria-current", "location");
          } else {
            link.removeAttribute("aria-current");
          }
        });
      }
      window.addEventListener("hashchange", updateActiveSection);
      updateActiveSection();

    </script>
  </body>
</html>