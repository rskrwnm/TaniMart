<?php
session_start();
if (!isset($_SESSION['username']) || ($_SESSION['role'] ?? '') !== 'farmer') {
  $loginPage = ($_SESSION['role'] ?? '') === 'user' ? '../html/index.html' : '../html/farmer-login.html';
  header('Location: ' . $loginPage);
  exit();
}

require_once('cnkt.php');

if (empty($_SESSION['product_form_token'])) {
  $_SESSION['product_form_token'] = bin2hex(random_bytes(32));
}

$productMessage = $_SESSION['product_message'] ?? '';
$productMessageType = $_SESSION['product_message_type'] ?? 'error';
unset($_SESSION['product_message'], $_SESSION['product_message_type']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
  $redirect = 'Location: add-product.php';
  $setProductMessage = static function (string $message, string $type = 'error'): void {
    $_SESSION['product_message'] = $message;
    $_SESSION['product_message_type'] = $type;
  };

  $token = $_POST['product_form_token'] ?? '';
  if (!hash_equals($_SESSION['product_form_token'], $token)) {
    $setProductMessage('Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.');
  } else {
    $name = trim($_POST['product_name'] ?? '');
    $price = $_POST['product_price'] ?? '';
    $quantity = $_POST['product_qty'] ?? '';
    $image = $_FILES['product_image'] ?? null;

    if ($name === '' || strlen($name) > 255) {
      $setProductMessage('Nama produk wajib diisi dan maksimal 255 karakter.');
    } elseif (!is_numeric($price) || (float) $price <= 0) {
      $setProductMessage('Harga harus berupa angka lebih dari 0.');
    } elseif (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 0) {
      $setProductMessage('Stok harus berupa bilangan bulat 0 atau lebih.');
    } elseif (!$image || $image['error'] !== UPLOAD_ERR_OK) {
      $setProductMessage('Pilih gambar produk yang valid untuk diunggah.');
    } elseif ($image['size'] > 5 * 1024 * 1024) {
      $setProductMessage('Ukuran gambar maksimal 5 MB.');
    } else {
      $imageInfo = getimagesize($image['tmp_name']);
      $imageTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
      ];
      $mimeType = $imageInfo['mime'] ?? '';

      if (!$imageInfo || !isset($imageTypes[$mimeType])) {
        $setProductMessage('Format gambar harus JPG, PNG, atau WEBP.');
      } else {
        $uploadDirectory = __DIR__ . '/../image/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
          $setProductMessage('Folder penyimpanan gambar tidak dapat dibuat.');
        } else {
          $fileName = bin2hex(random_bytes(16)) . '.' . $imageTypes[$mimeType];
          $filePath = $uploadDirectory . '/' . $fileName;

          if (!move_uploaded_file($image['tmp_name'], $filePath)) {
            $setProductMessage('Gambar produk gagal disimpan. Silakan coba lagi.');
          } else {
            try {
              $productCode = 'PRD' . strtoupper(bin2hex(random_bytes(5)));
              $stmt = $conn->prepare(
                'INSERT INTO product (product_image, product_name, product_price, product_qty, product_code) VALUES (?, ?, ?, ?, ?)'
              );
              $stmt->execute([
                '../image/uploads/' . $fileName,
                $name,
                number_format((float) $price, 2, '.', ''),
                (int) $quantity,
                $productCode,
              ]);
              $_SESSION['product_form_token'] = bin2hex(random_bytes(32));
              $_SESSION['product_message'] = 'Produk berhasil ditambahkan dan sekarang tersedia di katalog.';
              $_SESSION['product_message_type'] = 'success';
              $redirect = 'Location: home-farmer.php';
            } catch (PDOException $exception) {
              unlink($filePath);
              error_log('Failed to add farmer product: ' . $exception->getMessage());
              $setProductMessage('Produk gagal disimpan ke database. Periksa tabel produk lalu coba lagi.');
            }
          }
        }
      }
    }
  }

  header($redirect);
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Produk | TaniMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
    <style>
      :root { --forest: #163a24; --leaf: #2e7d45; --leaf-dark: #22633a; --marigold: #f4b72f; --paper: #f2f6ec; --ink: #1b2a1f; --muted: #667467; --line: #d5dfcb; }
      * { box-sizing: border-box; margin: 0; padding: 0; }
      body { min-height: 100vh; background: var(--paper); color: var(--ink); font-family: "Nunito Sans", system-ui, sans-serif; }
      .sidebar { position: fixed; inset: 0 auto 0 0; width: 250px; padding: 28px 18px; background: var(--forest); color: #fff; }
      .sidebar-brand { display: flex; align-items: center; gap: 12px; margin: 0 8px 34px; font-family: "Bricolage Grotesque", sans-serif; font-size: 22px; font-weight: 800; }
      .brand-mark { display: grid; flex: none; width: 42px; height: 42px; place-items: center; border: 2px solid rgba(255,255,255,.35); border-radius: 16px 16px 16px 5px; background: var(--leaf); font-size: 18px; }
      nav { display: grid; gap: 8px; }
      nav a { display: flex; align-items: center; gap: 12px; padding: 13px 14px; border-radius: 12px; color: #e4efd8; font-weight: 700; text-decoration: none; }
      nav a:hover, nav a:focus-visible, nav a[aria-current="page"] { background: var(--leaf); color: #fff; }
      nav a:focus-visible, button:focus-visible, input:focus-visible, .back:focus-visible { outline: 3px solid var(--marigold); outline-offset: 2px; }
      nav i { width: 20px; text-align: center; }
      main { width: min(760px, calc(100% - 300px)); margin: 0 auto 0 calc(250px + (100% - 250px - min(760px, 100% - 300px)) / 2); padding: 56px 0; }
      h1 { margin-bottom: 8px; color: var(--forest); font-family: "Bricolage Grotesque", sans-serif; font-size: clamp(26px, 4vw, 36px); }
      .intro { margin-bottom: 24px; color: var(--muted); }
      .form-card { padding: 26px; border-radius: 20px; background: #fff; box-shadow: 0 12px 28px -18px rgba(22,58,36,.5); }
      .message { margin-bottom: 18px; padding: 12px 14px; border-radius: 10px; font-weight: 700; }
      .message.success { background: #e3ecd6; color: var(--forest); }
      .message.error { background: #fde8e5; color: #8c241a; }
      form { display: grid; gap: 16px; }
      label { display: grid; gap: 6px; color: var(--forest); font-weight: 700; }
      input { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid var(--line); border-radius: 10px; color: var(--ink); font: inherit; }
      input:focus { border-color: var(--leaf); }
      button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 11px 16px; border: 0; border-radius: 12px; background: var(--leaf); color: #fff; font: inherit; font-weight: 800; cursor: pointer; }
      button:hover { background: var(--leaf-dark); }
      .back { display: inline-block; margin-top: 20px; color: var(--leaf-dark); font-weight: 700; text-decoration: none; }
      @media (max-width: 760px) {
        .sidebar { position: static; width: 100%; padding: 12px 16px; }
        .sidebar-brand { margin: 0 0 10px; font-size: 19px; }
        .brand-mark { width: 36px; height: 36px; }
        nav { display: flex; flex-wrap: wrap; gap: 6px; }
        nav a { flex: 1 1 auto; justify-content: center; padding: 10px; font-size: 14px; }
        main { width: min(100% - 32px, 760px); margin: 0 auto; padding: 32px 0; }
        .form-card { padding: 20px; }
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
        <a href="home-farmer.php#products"><i class="fas fa-box-open"></i>Produk</a>
        <a href="add-product.php" aria-current="page"><i class="fas fa-plus-circle"></i>Tambah Produk</a>
        <a href="home-farmer.php#delivery"><i class="fas fa-truck"></i>Pengiriman</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Keluar</a>
      </nav>
    </aside>
    <main>
      <h1>Tambah Produk Baru</h1>
      <p class="intro">Isi informasi produk agar tampil di katalog belanja.</p>
      <section class="form-card">
        <?php if ($productMessage !== ''): ?>
        <div class="message <?= htmlspecialchars($productMessageType, ENT_QUOTES, 'UTF-8') ?>" role="alert">
          <?= htmlspecialchars($productMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="product_form_token" value="<?= htmlspecialchars($_SESSION['product_form_token'], ENT_QUOTES, 'UTF-8') ?>">
          <label>
            Nama produk
            <input type="text" name="product_name" maxlength="255" required>
          </label>
          <label>
            Harga (Rp)
            <input type="number" name="product_price" min="0.01" step="0.01" required>
          </label>
          <label>
            Stok
            <input type="number" name="product_qty" min="0" step="1" required>
          </label>
          <label>
            Foto produk (JPG, PNG, atau WEBP; maks. 5 MB)
            <input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" required>
          </label>
          <button type="submit" name="add_product"><i class="fas fa-save"></i>Simpan Produk</button>
        </form>
      </section>
      <a class="back" href="home-farmer.php"><i class="fas fa-arrow-left"></i> Kembali ke dashboard</a>
    </main>
  </body>
</html>
