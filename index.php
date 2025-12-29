<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SimpleShop</title>
  <style>
    body { margin:0; font-family:Arial, Helvetica, sans-serif; background:#f5f5f5; }
    header { background:#4f46e5; color:#fff; padding:16px 32px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:22px; }
    nav a { color:white; margin-left:16px; text-decoration:none; font-size:14px; cursor:pointer; }
    .container { padding:32px; }
    .products { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:24px; }
    .card { background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.08); overflow:hidden; }
    .card img { width:100%; height:160px; object-fit:cover; }
    .card-body { padding:16px; }
    .price { color:#16a34a; font-weight:bold; margin-bottom:12px; }
    button {
      padding: 6px 10px;
      border: none;
      border-radius: 6px;
      background-color: #4f46e5;
      color: white;
      cursor: pointer;
      font-size: 12px;
    }
    button:hover { background:#4338ca; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px; border-bottom:1px solid #ddd; text-align:left; }
    .hidden { display:none; }
    footer { text-align:center; padding:16px; background:#e5e7eb; margin-top:40px; }
    input, textarea { width:100%; padding:10px; margin-bottom:12px; }
    .qty-box {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #f1f5f9;
      padding: 4px 6px;
      border-radius: 12px;
    }

    .qty-btn {
      width: 22px;
      height: 22px;
      border-radius: 6px;
      padding: 0;
      font-size: 14px;
      line-height: 1;
    }

    .qty-number {
      min-width: 16px;
      text-align: center;
      font-size: 13px;
      font-weight: bold;
    }
    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #4f46e5;
      color: white;
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 14px;
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.3s ease;
      z-index: 9999;
    }

    .toast.show {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
</head>
<body>

<header>
  <h1>PolkeShop</h1>
  <nav>
    <a onclick="showSection('home')">Home</a>
    <a href="dashboard_user.php">Dashboard</a>
    <a onclick="showSection('cart')">Keranjang (<span id="cartCount">0</span>)</a>
  </nav>
</header>

<div class="container">

  <!-- HOME / PRODUK -->
  <div id="home">
    <h2>Produk</h2>
    <div class="products">
      <div class="card">
        <img src="38zBwcOA20240620095121.jpg.webp">
        <div class="card-body">
          <h3>Kaos Polos</h3>
          <div class="price">75000</div>
          <button onclick="addToCart('Kaos Polos',75000)">Tambah ke Keranjang</button>
        </div>
      </div>
      <div class="card">
        <img src="Sef7c1d79029c4400bab4a99578568800h.jpg_720x720q80.jpg">
        <div class="card-body">
          <h3>Sepatu Sneakers</h3>
          <div class="price">250000</div>
          <button onclick="addToCart('Sepatu Sneakers',250000)">Tambah ke Keranjang</button>
        </div>
      </div>
    </div>
  </div>

  <!-- KERANJANG -->
  <div id="cart" class="hidden">
    <h2>Keranjang Belanja</h2>
    <table>
      <thead>
        <tr><th>Produk</th><th>Harga</th></tr>
      </thead>
      <tbody id="cartItems"></tbody>
    </table>
    <h3>Total: Rp <span id="total">0</span></h3>
    <button onclick="showSection('checkout')">Checkout</button>
  </div>

    <!-- CHECKOUT -->
  <div id="checkout" class="hidden">
    <h2>Form Checkout</h2>

    <h3>Rincian Pesanan</h3>
    <table>
      <tbody>
        <tr><td>Total Harga Barang</td><td>Rp <span id="subtotalCheckout">0</span></td></tr>
        <tr><td>Ongkos Kirim</td><td>Rp <span id="shipping">15000</span></td></tr>
        <tr><th>Total Bayar</th><th>Rp <span id="grandTotal">0</span></th></tr>
      </tbody>
    </table>

    <h3>Data Pembayaran</h3>
    <p>Silakan lakukan pembayaran melalui transfer bank ke rekening penjual di bawah ini.</p>

    <h4>Rekening Seller</h4>
    <div style="background:#fff;padding:16px;border-radius:10px;margin-bottom:20px;">
      <p><b>Nama Pemilik:</b> PolkeShop Official</p>
      <p><b>Nomor Rekening:</b> 1234567890</p>
      <p><b>Bank:</b> BCA</p>
      <p style="font-size:13px;color:#555;">Transfer sesuai total pembayaran. Pesanan akan diproses setelah pembayaran dikonfirmasi.</p>

      <input type="text" name="depositor" id="depositor" placeholder="Nama Depositor (pengirim)" required>
      <input type="text" name="bankPengirim" id="bankPengirim" placeholder="Nama Bank Pengirim" required>
    </div>

    <h3>Data Pembeli</h3>
    <p style="font-size:13px;color:#555;">⚠️ Pastikan <b>nama depositor</b> dan <b>nama bank</b> sesuai dengan data saat transfer untuk memudahkan konfirmasi pembayaran.</p>
    <form action="save_order.php" method="POST"
    onsubmit="document.getElementById('totalInput').value =
    document.getElementById('grandTotal').innerText;">

    <!-- DATA PEMBAYARAN -->
    <input type="text" name="depositor" placeholder="Nama Depositor" required>
    <input type="text" name="bankPengirim" placeholder="Bank Pengirim" required>

    <!-- DATA PEMBELI -->
    <input type="text" name="nama" placeholder="Nama Lengkap" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="tel" name="telepon" placeholder="No Telepon" required>
    <textarea name="alamat" placeholder="Alamat Lengkap" required></textarea>

    <input type="hidden" name="total" id="totalInput">

    <button type="submit">Buat Pesanan</button>
</form>


  </div>

</div>

<footer>© 2025 PolkeShop</footer>

<script>
  let cart = []; // {name, price, qty}

  function addToCart(name, price) {
    const item = cart.find(p => p.name === name);
    if (item) {
      item.qty += 1;
    } else {
      cart.push({ name, price, qty: 1 });
    }
    document.getElementById('cartCount').innerText = cart.reduce((sum,i)=>sum+i.qty,0);
    showToast('Produk sudah masuk ke keranjang');
  }

  function showSection(section) {
    ['home','cart','checkout'].forEach(id => document.getElementById(id).classList.add('hidden'));
    document.getElementById(section).classList.remove('hidden');
    if(section==='cart') renderCart();
  }

  function renderCart() {
    let tbody = document.getElementById('cartItems');
    tbody.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
      const subtotal = item.price * item.qty;
      total += subtotal;
      tbody.innerHTML += `
        <tr>
          <td>${item.name}</td>
          <td>Rp ${item.price}</td>
          <td>
            <div class="qty-box">
              <button class="qty-btn" onclick="decreaseQty(${index})">−</button>
              <span class="qty-number">${item.qty}</span>
              <button class="qty-btn" onclick="increaseQty(${index})">+</button>
            </div>
          </td>
          <td>Rp ${subtotal}</td>
          <td><button onclick="removeItem(${index})">Hapus</button></td>
        </tr>
      `;
    });

    document.getElementById('total').innerText = total;
    updateCheckoutSummary();
  }
  function updateCheckoutSummary() {
    const shipping = 15000;
    let subtotal = cart.reduce((sum, item) => sum + item.price, 0);
    document.getElementById('subtotalCheckout').innerText = subtotal;
    document.getElementById('shipping').innerText = shipping;
    document.getElementById('grandTotal').innerText = subtotal + shipping;
  }

  function submitOrder(e) {
  document.getElementById("totalHidden").value =
  document.getElementById("grandTotal").innerText;
  e.preventDefault();

  const dataOrder = {
    depositor: document.getElementById('depositor').value,
    bankPengirim: document.getElementById('bankPengirim').value,
    nama: document.getElementById('nama').value,
    email: document.getElementById('email').value,
    telepon: document.getElementById('telepon').value,
    alamat: document.getElementById('alamat').value,
    items: cart,
    total: document.getElementById('grandTotal').innerText
  };

  fetch('save_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dataOrder)
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === 'success') {
      alert('Pesanan berhasil disimpan!');
      cart = [];
      document.getElementById('cartCount').innerText = 0;
      showSection('home');
    } else {
      alert('Gagal: ' + res.message);
    }
  });
}

  function increaseQty(index) {
    cart[index].qty++;
    renderCart();
    document.getElementById('cartCount').innerText = cart.reduce((sum,i)=>sum+i.qty,0);
  }

  function decreaseQty(index) {
    cart[index].qty--;
    if (cart[index].qty <= 0) cart.splice(index,1);
    renderCart();
    document.getElementById('cartCount').innerText = cart.reduce((sum,i)=>sum+i.qty,0);
  }

  function removeItem(index) {
    cart.splice(index,1);
    renderCart();
    document.getElementById('cartCount').innerText = cart.reduce((sum,i)=>sum+i.qty,0);
  }

</script>

  <div id="toast" class="toast"></div>

  <script>
    function showToast(message) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.classList.add('show');

      setTimeout(() => {
        toast.classList.remove('show');
      }, 2000);
    }
  </script>
</body>
</html>
