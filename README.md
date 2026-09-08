# Pusat Plastik Wijaya

<div align="center">
  <a href="https://pusatplastikwijaya.com/products" target="_blank">
    <img src="docs/homepage-customer-preview.svg" alt="Homepage customer preview" width="1000" />
  </a>
</div>

Website e-commerce untuk toko plastik yang membantu pelanggan dalam mencari produk, melakukan checkout, dan memantau status pesanan secara online.

Website: [Pusat Plastik Wijaya](https://pusatplastikwijaya.com/products)

## Preview

<div align="center">
  <img src="screenshots/home_loggedIn.png" alt="Homepage customer logged in" width="502" />
</div>

<div align="center">
  <img src="screenshots/product.png" alt="Product page" width="502" />
  <img src="screenshots/admin_dashboard.png" alt="Admin dashboard" width="502" />
</div>

## Tentang Proyek

Pusat Plastik Wijaya adalah platform belanja online yang dirancang untuk mempermudah proses pembelian produk plastik secara cepat dan efisien. Dalam proyek ini, saya membangun sistem yang mencakup:

- katalog produk dengan kategori dan pencarian
- keranjang belanja dan validasi stok
- proses checkout dengan data pengiriman
- integrasi pembayaran menggunakan Midtrans
- riwayat pesanan dan status pembayaran
- dashboard admin untuk mengelola produk, pesanan, dan laporan

## Fitur Utama

### Pelanggan

- Menampilkan produk berdasarkan kategori dan pencarian
- Detail produk lengkap dengan harga, stok, dan deskripsi
- Keranjang belanja dengan perhitungan total otomatis
- Checkout yang mencakup alamat pengiriman dan metode pengiriman
- Pembayaran online melalui Midtrans
- Halaman riwayat pesanan dan detail transaksi
- Sistem poin pelanggan untuk potongan harga

### Admin

- Manajemen produk dan kategori
- Import serta export data produk
- Dashboard ringkasan penjualan
- Kelola status pesanan dan proses pembayaran
- Laporan produk terlaris dan performa penjualan
- Cetak invoice pesanan

## Tech Stack

- Laravel 11
- PHP 8.2
- MySQL
- Tailwind CSS
- Vite
- Midtrans API
- JavaScript
- RajaOngkir API

## Alur Aplikasi

1. Pelanggan membuka katalog produk.
2. Produk dipilih dan dimasukkan ke keranjang.
3. Pelanggan melakukan checkout dengan detail pengiriman.
4. Sistem menghitung total belanja dan memproses pembayaran.
5. Admin memantau pesanan dan mengelola data stok serta laporan.

## Struktur Proyek

- `app/` — logika aplikasi, controller, dan model
- `resources/views/` — tampilan frontend customer dan admin
- `routes/web.php` — routing aplikasi
- `database/` — migrasi dan seeder
- `public/` — aset publik
- `config/` — konfigurasi aplikasi dan layanan eksternal

