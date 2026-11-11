# Kumo Ramen

Website kedai ramen 3D untuk Lhokseumawe, dibuat oleh **Ahmad Umraithi**. Tampilannya mengikuti referensi Dribbble: latar hijau tua, panel krem bertepi awan, judul besar dengan mangkuk 3D di tengahnya, dan pita kuning yang berjalan.

Online di **https://ramen.kadaferhidup.my.id** (GitHub Pages).

## Isi halaman

1. **Hero**: judul "Semangkuk ramen hangat penuh rasa" dengan mangkuk ramen 3D yang berputar pelan di tengah judul, uap tipis, dan efek parallax kursor. Di bawahnya ada pita kuning berjalan.
2. **Ramen andalan**: tiga menu favorit dengan harga dan tombol tambah ke keranjang.
3. **Kenapa Kumo**: panel krem; mangkuk 3D yang sama muncul lagi di sini bersama tiga alasan.
4. **Menu lengkap**: 13 menu dengan filter (berkuah, pedas, dingin, tanpa daging), ditambah topping dan minuman.
5. **Menu 3D**: lima menu dalam 3D yang bisa diputar dengan jari, mouse, atau tombol panah, lalu ditambahkan ke keranjang dari sana. Tori Paitan Jamur, Shoyu Ajitama, dan dua Shin Ramyun hanya tampil di sini (`grid: false` di `js/data.js`).
6. **Reservasi**: form reservasi meja (tanggal, jam sesuai jam buka, jumlah orang, tempat duduk) yang dikirim ke WhatsApp.
7. **Info**: jam buka (hari ini ditandai), cara pesan dan bayar, lokasi.

## Pelayanan

- **Keranjang** yang tersimpan di perangkat pembeli, dengan pilihan **level pedas 0–5** per menu.
- Pilihan **diantar, ambil sendiri, atau makan di tempat**, serta cara bayar (QRIS, transfer, tunai).
- Pesanan dan reservasi tersusun rapi di **WhatsApp**: nama menu, jumlah, level pedas, subtotal, alamat, cara bayar.
- Label **Buka/Tutup** otomatis menurut jam Asia/Jakarta (Jumat buka setelah salat Jumat).
- Nama dan alamat pembeli diingat untuk pesanan berikutnya (hanya di perangkat itu).
- Kalau perangkat tidak mendukung 3D, semua model diganti gambar diam. Bisa dipakai dengan keyboard dan menghormati pengaturan *reduced motion*.

## Backend

Folder `backend/` berisi backend CodeIgniter 4 + MySQL: menu dari database, pesanan dan reservasi tersimpan dengan kode, dan panel admin di `/admin` (dashboard, pesanan, reservasi, kelola menu dengan upload foto dan tombol Habis). Website mendeteksi sendiri apakah backend aktif (`api/health`); kalau tidak, pesanan langsung lewat WhatsApp. Cara memasang ada di [backend/README.md](backend/README.md).

## Mengubah isi

Semua data ada di `js/data.js`:

- `SHOP`: nama kedai, nomor WhatsApp, kota, alamat, tautan Google Maps, Instagram, jam buka.
- `MENU`: nama, harga, kategori, level pedas bawaan, gambar, deskripsi.
- `EXTRAS`: topping dan minuman.

## Struktur

```
index.html
css/style.css
js/app.js        tampilan, keranjang, reservasi, jam buka
js/api.js        penghubung ke backend (opsional)
js/data.js       data kedai dan menu
js/scene3d.js    satu kanvas WebGL untuk semua objek 3D
js/models.js     daftar model 3D, kredit, dan penyesuaian warna
js/glb.js        pembaca file .glb tanpa library tambahan
vendor/          three.js r188 (tidak perlu internet)
models/          5 model 3D (.glb) yang sudah dioptimasi
img/menu/        14 foto menu tanpa background (.webp)
img/3d/          gambar diam dari model 3D
backend/         backend CodeIgniter 4 (lihat backend/README.md)
```

## Menjalankan di komputer

Website memakai modul JavaScript, jadi harus dibuka lewat server, bukan klik dua kali file `index.html`:

- VS Code: klik kanan `index.html` → *Open with Live Server*, atau
- Terminal: `python -m http.server 8000`, lalu buka http://localhost:8000

## Model 3D dan foto

Model 3D berasal dari Sketchfab dengan lisensi **CC BY 4.0**. Nama pembuatnya wajib dicantumkan dan sudah ada di footer website dan di bawah penampil 3D:

| Menu | Model | Pembuat |
|---|---|---|
| Ramen Spesial Kumo | [Ramen](https://sketchfab.com/3d-models/ramen-4be3c4e905434f708963fee985931038) | BlackBox613 |
| Tori Paitan Jamur | [3D Ramen](https://sketchfab.com/3d-models/3d-ramen-e912b8f2da144a3fb997a3802df64806) | PaulineRL |
| Shoyu Ajitama | [Stylized Ramen Bowl 3D Model [Rapid Assets]](https://sketchfab.com/3d-models/stylized-ramen-bowl-3d-model-rapid-assets-f6e2e0ceed104655ba9d44a44d51d66b) | RapidAssets |
| Shin Ramyun Big Bowl | [Ramen](https://sketchfab.com/3d-models/ramen-18979b3689f74806925258b42b66c319) | HyperPickle |
| Shin Ramyun Cup | [HGU_Korean Cup Noodle Shin Ramen](https://sketchfab.com/3d-models/hgu-korean-cup-noodle-shin-ramen-5c00fe74ee4943dc985471d16c95f9e5) | s_sugie |

Supaya ringan di HP, model disederhanakan (total segitiga turun dari ±1 juta menjadi ±240 ribu) dan teksturnya dikompres: ukuran total dari 44 MB menjadi 7,4 MB. Hanya model mangkuk utama (2 MB) yang dimuat di awal; model lain dimuat saat dipilih. Warna kuah model utama diubah menjadi merah pekat.

Foto menu disediakan pemilik website; background-nya sudah dihapus.

## Domain

File `CNAME` berisi `ramen.kadaferhidup.my.id`. Di Domainesia, buat record **CNAME** dengan nama `ramen` yang mengarah ke `kadaferhidup.github.io`, lalu aktifkan GitHub Pages (branch `main`, folder root) di pengaturan repo.

## Kontak

WhatsApp 0822-7607-8880 · Lhokseumawe, Aceh
