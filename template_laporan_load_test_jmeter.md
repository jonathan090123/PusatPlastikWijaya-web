# Template Laporan Load Test JMeter

## Identitas Pengujian

- Aplikasi: Pusat Plastik Wijaya
- Tools: Apache JMeter
- Parameter yang diukur: Response Time, Error Rate, Throughput
- Skenario beban: 10, 50, 100 virtual users

## Endpoint Yang Diuji

Endpoint utama yang disarankan untuk pengujian:

1. `/` - Homepage
2. `/products` - Katalog produk
3. `/login` - Login pengguna
4. `/checkout` - Proses checkout
5. `/admin/reports` - Laporan admin

## Tabel Hasil Pengujian

| Endpoint | Virtual Users | Samples | Average (ms) | Min (ms) | Max (ms) | Std. Dev. | Error Rate | Throughput | Kesimpulan |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: | --- |
| / | 10 | 80 | 52 | 39 | 136 | 19.66 | 0.00% | 10.6/sec | Sangat baik dan stabil |
| / | 50 |  |  |  |  |  |  |  |  |
| / | 100 |  |  |  |  |  |  |  |  |
| /products | 10 |  |  |  |  |  |  |  |  |
| /products | 50 |  |  |  |  |  |  |  |  |
| /products | 100 |  |  |  |  |  |  |  |  |
| /login | 10 |  |  |  |  |  |  |  |  |
| /login | 50 |  |  |  |  |  |  |  |  |
| /login | 100 |  |  |  |  |  |  |  |  |
| /checkout | 10 |  |  |  |  |  |  |  |  |
| /checkout | 50 |  |  |  |  |  |  |  |  |
| /checkout | 100 |  |  |  |  |  |  |  |  |
| /admin/reports | 10 |  |  |  |  |  |  |  |  |
| /admin/reports | 50 |  |  |  |  |  |  |  |  |
| /admin/reports | 100 |  |  |  |  |  |  |  |  |

## Data Yang Perlu Ditulis Di Laporan

Untuk setiap hasil Summary Report, catat nilai berikut:

1. Endpoint yang diuji
2. Jumlah virtual users
3. Jumlah samples
4. Average response time
5. Min response time
6. Max response time
7. Std. Dev.
8. Error rate
9. Throughput
10. Kesimpulan singkat

## Contoh Analisis Hasil Homepage 10 User

Pada pengujian endpoint `/` dengan 10 virtual users, diperoleh 80 samples dengan rata-rata response time sebesar 52 ms. Nilai response time minimum tercatat 39 ms dan maksimum 136 ms, dengan standar deviasi 19.66. Error rate sebesar 0.00% menunjukkan bahwa tidak terdapat request yang gagal selama pengujian. Nilai throughput sebesar 10.6 request per second menunjukkan bahwa sistem masih mampu menangani beban rendah dengan sangat baik. Berdasarkan hasil tersebut, homepage dapat dikategorikan stabil dan memiliki performa yang sangat baik pada skenario 10 user.

## Format Narasi Metodologi

Pengujian performa dilakukan menggunakan Apache JMeter dengan tiga skenario beban, yaitu 10, 50, dan 100 virtual users. Parameter yang diukur meliputi response time, error rate, dan throughput. Pengujian diterapkan pada beberapa endpoint utama aplikasi, yaitu homepage, katalog produk, login, checkout, dan laporan admin. Hasil dari Summary Report dicatat untuk dianalisis tingkat kestabilan aplikasi pada masing-masing skenario.

## Format Kesimpulan Per Hasil

- `Sangat baik dan stabil`: error 0% dan average sangat rendah
- `Baik dan stabil`: error 0% dan average masih wajar
- `Mulai melambat`: error 0% tetapi average naik cukup besar
- `Kurang stabil`: error mulai muncul atau variasi response time besar
- `Tidak stabil`: error tinggi atau response time sangat besar