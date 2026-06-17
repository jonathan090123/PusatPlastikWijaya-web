# Alur Customer - Pusat Plastik Wijaya

---

## Halaman Produk (Katalog)

Customer buka halaman produk → sistem ambil data produk aktif dari database, kalau ada search sistem cari berdasarkan nama produk/kode/kategori, kalau klik tombol preview dropdown sistem ambil 8 produk paling cocok via AJAX.

---

## Halaman Cart (Keranjang)

Customer klik keranjang → sistem ambil item yang sudah disimpan dari database, customer bisa tambah/kurang jumlah atau hapus item, kalau klik "Beli Langsung" item langsung masuk ke keranjang lalu pindah ke halaman checkout.

---

## Halaman Checkout

Customer klik "Buat Pesanan" → sistem buat order di tabel orders dengan generate invoice. Kedua, masukkan item ke tabel order_items dan kurangi stok di tabel products. Ketiga, kosongkan keranjang di cart_items. Keempat, kalau customer pakai poin, kurangi poin di users dan catat history ke point_histories. Kalau semua berhasil, redirect ke halaman pembayaran.

---

## Halaman Pembayaran (Payment)

Customer buka halaman pembayaran → sistem ambil data order dari database, lalu minta Snap Token ke Midtrans. Customer bayar di popup Midtrans, setelah itu Midtrans kirim notifikasi ke server via webhook. Sistem catat status pembayaran di tabel payments, kalau berhasil update order jadi "processing", kalau gagal kembalikan stok ke products dan poin ke users.

---

## Halaman Order (Pesanan Saya)

Customer buka halaman pesanan → sistem ambil list order dari database, kalau ada order yang lewat deadline otomatis jadi expired dan stok/poin dikembalikan. Customer bisa klik "Pesanan Diterima" → order selesai dan poin dicairkan. Customer bisa batalkan → stok dan poin dikembalikan. Customer bisa "Beli Lagi" → item pindah ke keranjang.

---

## Ringkasan Singkat per Halaman

| Halaman | Satu Kalimat |
|---------|--------------|
| **Produk** | Ambil data produk dari database, bisa search dan preview dropdown. |
| **Cart** | Ambil item dari database, bisa tambah/kurang/hapus, lalu ke checkout. |
| **Checkout** | Buat order di database, kurangi stok, kosongkan cart, kurangi poin, lalu ke payment. |
| **Payment** | Ambil order, minta token ke Midtrans, customer bayar, webhook update status. |
| **Order** | Ambil list order, auto-expire deadline, bisa selesai/batal/beli lagi. |
