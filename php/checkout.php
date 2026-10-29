<?php
	require 'cartSystemAccess.php';

	$grand_total = 0;
	$allItems = '';
	$items = [];

	$sql = "SELECT CONCAT(product_name, '(',qty,')') AS ItemQty, total_price FROM cart";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	while ($row = $result->fetch_assoc()) {
	  $grand_total += $row['total_price'];
	  $items[] = $row['ItemQty'];
	}
	$allItems = implode(', ', $items);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="author" content="Sahil Kumar">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Pembayaran | TaniMart</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css' />
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css' />
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
    body { font-family: "Nunito Sans", system-ui, sans-serif; color: var(--ink); background: var(--paper); }
    h1, h2, h5, .brand-name { font-family: "Bricolage Grotesque", "Nunito Sans", sans-serif; letter-spacing: -0.02em; }
    a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline: 3px solid var(--marigold); outline-offset: 2px; }

    /* Navbar */
    .site-nav { background: rgba(242, 246, 236, .94); backdrop-filter: blur(8px); border-bottom: 1px solid var(--line); min-height: 68px; }
    .site-nav .navbar-brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 22px; color: var(--forest); }
    .brand-mark { width: 36px; height: 36px; border-radius: 11px 11px 11px 4px; background: var(--leaf); color: #fff; display: grid; place-items: center; font-size: 16px; }
    .site-nav .nav-link { padding: 8px 14px !important; margin-left: 4px; border-radius: 10px; font-weight: 600; font-size: 15px; color: var(--ink) !important; transition: background .2s; }
    .site-nav .nav-link:hover { background: #e3ecd6; }
    .site-nav .nav-link.active { background: var(--forest); color: #fff !important; }
    .site-nav .nav-link i { margin-right: 6px; }
    .navbar-toggler { border: 1.5px solid var(--line); border-radius: 10px; }
    #cart-item { position: relative; top: -8px; left: -4px; font-size: .7rem; background: var(--marigold); color: var(--forest); }
    #cart-item:empty { display: none; }

    /* Page header */
    .page-head { position: relative; overflow: hidden; background: var(--forest); color: #fff; padding: 40px 0 44px; }
    .page-head::after { content: ""; position: absolute; width: 220px; height: 220px; border-radius: 50%; background: var(--marigold); right: 6%; top: -110px; }
    .page-head .container { position: relative; z-index: 1; }
    .page-head h1 { font-size: clamp(28px, 4vw, 40px); font-weight: 800; margin-bottom: 6px; }
    .page-head p { color: #e4efd8; margin: 0; max-width: 46ch; }

    /* Ringkasan & form */
    .panel { background: #fff; border-radius: 20px; padding: 24px; box-shadow: 0 12px 28px -18px rgba(22, 58, 36, .5); }
    .summary h2 { font-size: 20px; font-weight: 800; color: var(--forest); margin-bottom: 12px; }
    .summary ul { list-style: none; padding: 0; margin: 0 0 14px; }
    .summary li { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px dashed var(--line); }
    .summary li i { color: var(--leaf); width: 16px; }
    .summary .row-line { display: flex; justify-content: space-between; align-items: baseline; padding: 6px 0; color: var(--muted); }
    .summary .free { color: var(--leaf-dark); font-weight: 700; }
    .summary .total { border-top: 2px solid var(--line); margin-top: 6px; padding-top: 12px; color: var(--ink); font-weight: 700; }
    .summary .total strong { font-family: "Bricolage Grotesque", sans-serif; font-size: 26px; font-weight: 800; color: var(--leaf-dark); }

    .form-section-title { font-family: "Bricolage Grotesque", sans-serif; font-size: 20px; font-weight: 800; color: var(--forest); margin-bottom: 14px; }
    label { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
    .form-control { height: 46px; border-radius: 12px; border: 1.5px solid transparent; background: var(--paper); }
    textarea.form-control { height: auto; }
    .form-control:focus { background: #fff; border-color: var(--leaf); box-shadow: 0 0 0 4px rgba(46, 125, 69, .15); }
    .btn-order { height: 50px; border: 0; border-radius: 12px; background: var(--marigold); color: var(--forest); font-weight: 800; font-size: 16px; box-shadow: 0 10px 20px -10px rgba(0, 0, 0, .5); transition: background .2s, transform .15s; }
    .btn-order:hover { background: #ffc94d; color: var(--forest); }
    .btn-order:active { transform: scale(.98); }
    .btn-leaf { border: 0; border-radius: 12px; background: var(--leaf); color: #fff; font-weight: 700; padding: 12px 24px; }
    .btn-leaf:hover { background: var(--leaf-dark); color: #fff; }

    /* Respons dari action.php setelah pesanan dikirim */
    #order .jumbotron { background: #fff; border-radius: 20px; box-shadow: 0 12px 28px -18px rgba(22, 58, 36, .5); }
    #order .btn-info { background: var(--leaf); border: 0; border-radius: 12px; }

    .empty { text-align: center; padding: 48px 16px; color: var(--muted); }
    .empty i { font-size: 48px; color: #9bb88a; margin-bottom: 12px; }

    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-md navbar-light site-nav sticky-top">
    <div class="container">
      <a class="navbar-brand" href="../html/index.html">
        <span class="brand-mark"><i class="fas fa-leaf"></i></span>
        <span class="brand-name">TaniMart</span>
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar" aria-label="Buka menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php"><i class="fas fa-store"></i>Produk</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="checkout.php"><i class="fas fa-money-check-alt"></i>Pembayaran</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="cart.php" aria-label="Keranjang belanja">
              <span class="position-relative">
                <i class="fas fa-shopping-cart"></i>
                <span id="cart-item" class="badge badge-pill"></span>
              </span>
              Keranjang
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Judul halaman -->
  <header class="page-head">
    <div class="container">
      <h1>Selesaikan pesanan Anda</h1>
      <p>Periksa ringkasan belanja, isi data pengiriman, lalu pilih cara pembayaran.</p>
    </div>
  </header>

  <main class="container my-4">
    <div class="row justify-content-center">
      <div class="col-lg-7 pb-4" id="order">
        <?php if (count($items) === 0): ?>
        <div class="panel empty">
          <i class="fas fa-shopping-basket d-block"></i>
          <h2 class="h5">Keranjang Anda masih kosong</h2>
          <p>Tambahkan produk terlebih dahulu sebelum checkout.</p>
          <a href="index.php" class="btn btn-leaf">Mulai belanja</a>
        </div>
        <?php else: ?>
        <div class="panel summary mb-4">
          <h2>Ringkasan belanja</h2>
          <ul>
            <?php foreach ($items as $item): ?>
            <li><i class="fas fa-leaf"></i><?= htmlspecialchars($item) ?></li>
            <?php endforeach; ?>
          </ul>
          <div class="row-line"><span>Ongkos kirim</span><span class="free">Gratis</span></div>
          <div class="row-line total"><span>Total pembayaran</span><strong>Rp<?= number_format($grand_total, 0, ',', '.') ?></strong></div>
        </div>

        <form action="" method="post" id="placeOrder" class="panel">
          <input type="hidden" name="products" value="<?= htmlspecialchars($allItems) ?>">
          <input type="hidden" name="grand_total" value="<?= htmlspecialchars($grand_total) ?>">
          <h2 class="form-section-title">Data pengiriman</h2>
          <div class="form-group">
            <label for="f-name">Nama lengkap</label>
            <input type="text" id="f-name" name="name" class="form-control" placeholder="Contoh: Budi Santoso" autocomplete="name" required>
          </div>
          <div class="form-group">
            <label for="f-email">Email</label>
            <input type="email" id="f-email" name="email" class="form-control" placeholder="nama@email.com" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label for="f-phone">Nomor telepon</label>
            <input type="tel" id="f-phone" name="phone" class="form-control" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
          </div>
          <div class="form-group">
            <label for="f-address">Alamat pengiriman</label>
            <textarea id="f-address" name="address" class="form-control" rows="3" cols="10" placeholder="Jalan, nomor rumah, kelurahan, kota" autocomplete="street-address" required></textarea>
          </div>
          <h2 class="form-section-title mt-4">Metode pembayaran</h2>
          <div class="form-group">
            <select name="pmode" class="form-control" aria-label="Metode pembayaran" required>
              <option value="" selected disabled>Pilih metode pembayaran</option>
              <option value="cod">Bayar di tempat (COD)</option>
              <option value="netbanking">Internet banking</option>
              <option value="cards">Kartu debit/kredit</option>
            </select>
          </div>
          <div class="form-group mb-0">
            <input type="submit" name="submit" value="Pesan sekarang" class="btn btn-order btn-block">
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js'></script>

  <script type="text/javascript">
  $(document).ready(function() {

    // Kirim data form ke server
    $("#placeOrder").submit(function(e) {
      e.preventDefault();
      $.ajax({
        url: 'action.php',
        method: 'post',
        data: $('form').serialize() + "&action=order",
        success: function(response) {
          $("#order").html(response);
        }
      });
    });

    // Tampilkan jumlah item di keranjang pada navbar
    load_cart_item_number();

    function load_cart_item_number() {
      $.ajax({
        url: 'action.php',
        method: 'get',
        data: {
          cartItem: "cart_item"
        },
        success: function(response) {
          $("#cart-item").html(response);
        }
      });
    }
  });
  </script>
</body>

</html>