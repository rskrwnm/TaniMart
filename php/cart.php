<?php
  session_start();
  $showAlert = isset($_SESSION['showAlert']) ? $_SESSION['showAlert'] : 'none';
  $alertMessage = isset($_SESSION['message']) ? $_SESSION['message'] : '';
  unset($_SESSION['showAlert']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="author" content="Sahil Kumar">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Keranjang | TaniMart</title>
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
      --danger: #c0392b;
    }
    body { font-family: "Nunito Sans", system-ui, sans-serif; color: var(--ink); background: var(--paper); }
    h1, h2, h5, .brand-name { font-family: "Bricolage Grotesque", "Nunito Sans", sans-serif; letter-spacing: -0.02em; }
    a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid var(--marigold); outline-offset: 2px; }

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

    .alert { border: 0; border-radius: 14px; box-shadow: 0 10px 24px -16px rgba(22, 58, 36, .6); }

    /* Tabel keranjang */
    .panel { background: #fff; border-radius: 20px; padding: 8px 24px 24px; box-shadow: 0 12px 28px -18px rgba(22, 58, 36, .5); }
    .cart-table { margin: 0; min-width: 640px; }
    .cart-table th { border: 0; border-bottom: 2px solid var(--line); padding: 18px 12px 12px; font-size: 13px; font-weight: 700; color: var(--muted); vertical-align: middle; }
    .cart-table td { border: 0; border-bottom: 1px solid var(--line); padding: 14px 12px; vertical-align: middle; }
    .cart-table .thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 12px; background: #e3ecd6; }
    .cart-table .name { font-weight: 700; }
    .cart-table .subtotal { font-family: "Bricolage Grotesque", sans-serif; font-weight: 800; color: var(--leaf-dark); white-space: nowrap; }
    .itemQty { width: 84px; height: 42px; border-radius: 12px; border: 1.5px solid transparent; background: var(--paper); font-weight: 600; }
    .itemQty:focus { background: #fff; border-color: var(--leaf); box-shadow: 0 0 0 4px rgba(46, 125, 69, .15); }
    .btn-clear { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; border: 1.5px solid var(--line); background: #fff; color: var(--danger); font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap; transition: background .2s; }
    .btn-clear:hover { background: #fdecea; color: var(--danger); text-decoration: none; }
    .btn-remove { display: inline-grid; place-items: center; width: 38px; height: 38px; border-radius: 50%; color: var(--danger); transition: background .2s; }
    .btn-remove:hover { background: #fdecea; color: var(--danger); }

    .cart-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding-top: 22px; }
    .grand { text-align: right; }
    .grand span { display: block; color: var(--muted); font-size: 14px; font-weight: 600; }
    .grand strong { font-family: "Bricolage Grotesque", sans-serif; font-size: 30px; font-weight: 800; color: var(--leaf-dark); line-height: 1.1; }
    .btn-soft { padding: 12px 22px; border-radius: 12px; border: 1.5px solid var(--line); background: #fff; color: var(--forest); font-weight: 700; transition: background .2s; }
    .btn-soft:hover { background: #e3ecd6; color: var(--forest); }
    .btn-checkout { padding: 13px 28px; border: 0; border-radius: 12px; background: var(--marigold); color: var(--forest); font-weight: 800; box-shadow: 0 10px 20px -10px rgba(0, 0, 0, .5); transition: background .2s, transform .15s; }
    .btn-checkout:hover { background: #ffc94d; color: var(--forest); }
    .btn-checkout:active { transform: scale(.98); }
    .btn-checkout.disabled { opacity: .5; box-shadow: none; }
    .btn-leaf { border: 0; border-radius: 12px; background: var(--leaf); color: #fff; font-weight: 700; padding: 12px 24px; }
    .btn-leaf:hover { background: var(--leaf-dark); color: #fff; }

    .empty { text-align: center; padding: 56px 16px 40px; color: var(--muted); }
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
            <a class="nav-link" href="../html/index.html"><i class="fas fa-home"></i>Beranda</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php"><i class="fas fa-store"></i>Produk</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="checkout.php"><i class="fas fa-money-check-alt"></i>Pembayaran</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="cart.php" aria-label="Keranjang belanja">
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
      <h1>Keranjang belanja</h1>
      <p>Atur jumlah atau hapus produk sebelum lanjut ke checkout.</p>
    </div>
  </header>

  <main class="container my-4">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div style="display:<?= htmlspecialchars($showAlert) ?>" class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">&times;</button>
          <strong><?= $alertMessage ?></strong>
        </div>

        <?php
          require 'cartSystemAccess.php';
          $stmt = $conn->prepare('SELECT * FROM cart');
          $stmt->execute();
          $result = $stmt->get_result();
          $grand_total = 0;
        ?>

        <?php if ($result->num_rows === 0): ?>
        <div class="panel empty">
          <i class="fas fa-shopping-basket d-block"></i>
          <h2 class="h5">Keranjang Anda masih kosong</h2>
          <p>Yuk, pilih sayur, buah, dan produk segar lainnya.</p>
          <a href="index.php" class="btn btn-leaf">Mulai belanja</a>
        </div>
        <?php else: ?>
        <div class="panel">
          <div class="table-responsive">
            <table class="table cart-table">
              <thead>
                <tr>
                  <th>Foto</th>
                  <th>Produk</th>
                  <th>Harga</th>
                  <th>Jumlah</th>
                  <th>Subtotal</th>
                  <th class="text-right">
                    <a href="action.php?clear=all" class="btn-clear" onclick="return confirm('Yakin ingin mengosongkan keranjang?');"><i class="fas fa-trash"></i> Kosongkan</a>
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><img class="thumb" src="<?= htmlspecialchars($row['product_image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" onerror="this.style.visibility='hidden'"></td>
                  <td class="name">
                    <?= htmlspecialchars($row['product_name']) ?>
                    <input type="hidden" class="pid" value="<?= htmlspecialchars($row['id']) ?>">
                    <input type="hidden" class="pprice" value="<?= htmlspecialchars($row['product_price']) ?>">
                  </td>
                  <td>Rp<?= number_format($row['product_price'], 0, ',', '.') ?></td>
                  <td><input type="number" min="1" class="form-control itemQty" value="<?= htmlspecialchars($row['qty']) ?>" aria-label="Jumlah"></td>
                  <td class="subtotal">Rp<?= number_format($row['total_price'], 0, ',', '.') ?></td>
                  <td class="text-right">
                    <a href="action.php?remove=<?= urlencode($row['id']) ?>" class="btn-remove" aria-label="Hapus produk" onclick="return confirm('Yakin ingin menghapus produk ini?');"><i class="fas fa-trash-alt"></i></a>
                  </td>
                </tr>
                <?php $grand_total += $row['total_price']; ?>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <div class="cart-bar">
            <a href="index.php" class="btn btn-soft"><i class="fas fa-arrow-left"></i>&nbsp;&nbsp;Lanjut belanja</a>
            <div class="d-flex align-items-center flex-wrap" style="gap:20px;">
              <div class="grand"><span>Total belanja</span><strong>Rp<?= number_format($grand_total, 0, ',', '.') ?></strong></div>
              <a href="checkout.php" class="btn btn-checkout <?= ($grand_total > 0) ? '' : 'disabled'; ?>"><i class="far fa-credit-card"></i>&nbsp;&nbsp;Lanjut Pembayaran</a>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js'></script>

  <script type="text/javascript">
  $(document).ready(function() {

    // Ubah jumlah produk, lalu muat ulang halaman setelah server selesai menyimpan
    $(".itemQty").on('change', function() {
      var $el = $(this).closest('tr');

      var pid = $el.find(".pid").val();
      var pprice = $el.find(".pprice").val();
      var qty = $el.find(".itemQty").val();
      $.ajax({
        url: 'action.php',
        method: 'post',
        cache: false,
        data: {
          qty: qty,
          pid: pid,
          pprice: pprice
        },
        success: function(response) {
          console.log(response);
          location.reload();
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