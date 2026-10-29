<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="author" content="Sahil Kumar">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Belanja | TaniMart</title>
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

    /* Message (response dari action.php) */
    #message .alert { border: 0; border-radius: 14px; box-shadow: 0 10px 24px -16px rgba(22, 58, 36, .6); }

    /* Produk */
    .card-product { border: 0; border-radius: 20px; overflow: hidden; background: #fff; height: 100%; box-shadow: 0 12px 28px -18px rgba(22, 58, 36, .5); transition: transform .25s, box-shadow .25s; }
    .card-product:hover { transform: translateY(-4px); box-shadow: 0 18px 32px -16px rgba(22, 58, 36, .55); }
    .thumb { background: #e3ecd6; }
    .thumb img { width: 100%; aspect-ratio: 4 / 3; height: auto; object-fit: cover; display: block; }
    .thumb.broken { aspect-ratio: 4 / 3; display: grid; place-items: center; }
    .thumb.broken img { display: none; }
    .thumb.broken::after { content: "\f06c"; font-family: "Font Awesome 5 Free"; font-weight: 900; font-size: 48px; color: #9bb88a; }
    .card-product .card-body { display: flex; flex-direction: column; padding: 18px 20px 20px; }
    .product-name { font-size: 20px; font-weight: 600; margin-bottom: 2px; color: var(--ink); }
    .product-price { font-family: "Bricolage Grotesque", sans-serif; font-weight: 800; font-size: 22px; color: var(--leaf-dark); margin-bottom: 14px; }
    .form-submit { margin-top: auto; }
    .form-submit label { font-size: 13px; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
    .form-control.pqty { height: 44px; border-radius: 12px; border: 1.5px solid transparent; background: var(--paper); font-weight: 600; }
    .form-control.pqty:focus { background: #fff; border-color: var(--leaf); box-shadow: 0 0 0 4px rgba(46, 125, 69, .15); }
    .addItemBtn { height: 46px; border: 0; border-radius: 12px; background: var(--leaf); color: #fff; font-weight: 700; box-shadow: 0 8px 18px -8px rgba(46, 125, 69, .8); transition: background .2s, transform .15s; }
    .addItemBtn:hover { background: var(--leaf-dark); color: #fff; }
    .addItemBtn:active { transform: scale(.97); }

    .empty { text-align: center; padding: 64px 16px; color: var(--muted); }
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
            <a class="nav-link active" href="index.php"><i class="fas fa-store"></i>Produk</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="checkout.php"><i class="fas fa-money-check-alt"></i>Pembayaran</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../html/index.html"><i class="fas fa-home"></i>Beranda</a>
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
      <h1>Belanja hasil tani segar</h1>
      <p>Pilih produk, atur jumlahnya, lalu tambahkan ke keranjang.</p>
    </div>
  </header>

  <!-- Daftar produk -->
  <main class="container my-4">
    <div id="message"></div>
    <div class="row">
      <?php
        include 'cartSystemAccess.php';
        $stmt = $conn->prepare('SELECT * FROM product');
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0):
      ?>
      <div class="col-12 empty">
        <i class="fas fa-seedling d-block"></i>
        <h2 class="h5">Belum ada produk</h2>
        <p>Produk akan muncul di sini setelah ditambahkan.</p>
      </div>
      <?php endif; ?>
      <?php while ($row = $result->fetch_assoc()): ?>
      <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="card card-product">
          <div class="thumb">
            <img src="<?= htmlspecialchars($row['product_image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>" loading="lazy" onerror="this.parentElement.classList.add('broken')">
          </div>
          <div class="card-body">
            <h5 class="product-name"><?= htmlspecialchars($row['product_name']) ?></h5>
            <p class="product-price">Rp<?= number_format($row['product_price'], 0, ',', '.') ?></p>
            <form action="" class="form-submit">
              <label>Jumlah
                <input type="number" min="1" class="form-control pqty" value="<?= htmlspecialchars($row['product_qty']) ?>">
              </label>
              <input type="hidden" class="pid" value="<?= htmlspecialchars($row['id']) ?>">
              <input type="hidden" class="pname" value="<?= htmlspecialchars($row['product_name']) ?>">
              <input type="hidden" class="pprice" value="<?= htmlspecialchars($row['product_price']) ?>">
              <input type="hidden" class="pimage" value="<?= htmlspecialchars($row['product_image']) ?>">
              <input type="hidden" class="pcode" value="<?= htmlspecialchars($row['product_code']) ?>">
              <button class="btn btn-block mt-2 addItemBtn" type="submit"><i class="fas fa-cart-plus"></i> Tambah ke keranjang</button>
            </form>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </main>

  <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js'></script>

  <script type="text/javascript">
  $(document).ready(function() {
    // Kirim detail produk ke server
    $(".addItemBtn").click(function(e) {
      e.preventDefault();
      var $form = $(this).closest(".form-submit");
      var pid = $form.find(".pid").val();
      var pname = $form.find(".pname").val();
      var pprice = $form.find(".pprice").val();
      var pimage = $form.find(".pimage").val();
      var pcode = $form.find(".pcode").val();
      var pqty = $form.find(".pqty").val();

      $.ajax({
        url: 'action.php',
        method: 'post',
        data: {
          pid: pid,
          pname: pname,
          pprice: pprice,
          pqty: pqty,
          pimage: pimage,
          pcode: pcode
        },
        success: function(response) {
          $("#message").html(response);
          window.scrollTo(0, 0);
          load_cart_item_number();
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