# Backend Kumo Ramen (CodeIgniter 4)

Backend opsional untuk website Kumo Ramen. Kalau aktif, website otomatis:

- mengambil **menu, harga, foto, dan status habis** dari database;
- menyimpan **pesanan** dari keranjang (kode `KR-261111-K7Q2`) dan **reservasi meja** (kode `RS-261111-AB3D`), lalu pembeli konfirmasi lewat WhatsApp dengan kode itu;
- menolak pesanan menu yang sedang habis, dan menghitung ulang harga di server (harga dari browser tidak dipakai).

Tanpa backend (GitHub Pages), website tetap jalan dan pesanan langsung dikirim lewat WhatsApp.

## Panel admin (`/admin`)

| Halaman | Isi |
|---|---|
| Dashboard | Pesanan baru, yang sedang dimasak, omzet dan porsi hari ini, reservasi berikutnya, menu terlaris |
| Pesanan | Kartu per pesanan: isi, level pedas, cara terima, alamat, cara bayar. Ubah status (Baru → Dimasak → Siap / diantar → Selesai, atau Batal), WhatsApp pembeli, hapus, unduh CSV untuk Excel |
| Reservasi | Daftar mendatang / hari ini / sudah lewat. Ubah status (Baru, Dikonfirmasi, Sudah datang, Batal) dan kirim konfirmasi WhatsApp |
| Menu | Tambah, ubah harga dan deskripsi, upload foto, atur kategori, level pedas bawaan, model 3D, tampil di Menu lengkap atau hanya di bagian 3D, tandai **Habis** dengan satu klik |

Keamanan: login dengan password ter-hash, batas percobaan login, token CSRF di semua form admin, batas jumlah kiriman API per IP, kolom jebakan untuk robot spam, dan upload foto hanya JPG/PNG/WebP maksimal 3 MB.

## Memasang di komputer (XAMPP / Laragon)

Butuh PHP 8.1+, Composer, dan MySQL.

```bash
cd backend
./install.sh          # Windows: install.bat
```

Installer mengunduh CodeIgniter 4, menyalin website (folder di atas `backend/`) ke `public/`, dan membuat `.env`. Lalu:

```bash
php spark db:create kumo_ramen
php spark migrate
php spark db:seed DatabaseSeeder
php spark serve
```

Buka http://localhost:8080 untuk website dan http://localhost:8080/admin untuk panel admin.

**Ganti `ADMIN_PASSWORD` di `.env` sebelum menjalankan seeder**, terutama di hosting. Email admin diambil dari `ADMIN_EMAIL`.

Kalau website (HTML/CSS/JS) diubah, jalankan installer lagi supaya `public/` ikut terbarui.

## Hosting

- **Satu server (disarankan):** upload folder `backend/` ke hosting PHP, arahkan domain ke `backend/public`, isi `.env` (database, `app.baseURL`, `CI_ENVIRONMENT = production`).
- **Website di GitHub Pages, backend di tempat lain:** tambahkan `<script>window.KUMO_API = 'https://domain-backend/api'</script>` sebelum `js/app.js` di `index.html`, dan isi `CORS_ORIGINS` di `.env` dengan `https://ramen.kadaferhidup.my.id`.

## API

| Method | Alamat | Isi |
|---|---|---|
| GET | `/api/health` | Cek backend aktif |
| GET | `/api/menu` | Daftar menu aktif |
| POST | `/api/orders` | `{items: [{id, qty, level}], name, phone, mode: antar/ambil/makan, address, pay: QRIS/Transfer/Tunai, note}` |
| POST | `/api/reservations` | `{name, phone, date: YYYY-MM-DD, time: HH:MM, people: "4 orang", area, note}` |

Jam buka, status, cara bayar, dan pilihan meja diatur di `app/Config/Store.php` (samakan jam buka dengan `js/data.js`).
