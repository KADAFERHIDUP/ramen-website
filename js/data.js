// Data kedai dan menu. Ubah di sini untuk mengganti nama, harga, jam buka, atau nomor WhatsApp.

export const SHOP = {
  name: 'Kumo Ramen',
  whatsapp: '6282276078880',          // format internasional tanpa tanda +
  whatsappLabel: '0822-7607-8880',
  city: 'Lhokseumawe, Aceh',
  address: '',                        // isi alamat lengkap kedai; kosong = tidak ditampilkan
  mapsUrl: '',                        // tautan Google Maps (opsional)
  instagram: '',                      // contoh: 'kumoramen.lsm' (opsional)
  // jam buka per hari (0 = Minggu). Jumat buka setelah salat Jumat.
  hours: {
    0: ['11:00', '22:00'], 1: ['11:00', '22:00'], 2: ['11:00', '22:00'], 3: ['11:00', '22:00'],
    4: ['11:00', '22:00'], 5: ['14:00', '22:00'], 6: ['11:00', '22:00'],
  },
  timeZone: 'Asia/Jakarta',
};

// cat: kuah, pedas, dingin, sayur, instan
// spicy: angka = level pedas bawaan (0–5) dan pembeli bisa mengubahnya; null = tidak ada pilihan pedas
export const MENU = [
  { id: 'ramen-spesial', name: 'Ramen Spesial Kumo', price: 49000, cat: ['kuah', 'pedas'], spicy: 2, img: 'img/3d/ramen-spesial.webp', model: 'ramen-spesial', badge: 'Paling laris',
    desc: 'Semua yang terbaik dalam satu mangkuk: sapi iris, jamur, jagung manis, nori, dua telur, cabai, dan kuah pedas gurih.' },
  { id: 'sapi-kimchi', name: 'Ramen Sapi Kimchi', price: 42000, cat: ['kuah', 'pedas'], spicy: 3, img: 'img/menu/sapi-kimchi.webp', signature: true,
    desc: 'Irisan sapi empuk, kimchi, sawi, dan telur ajitama di kuah merah gochujang.' },
  { id: 'miso-jagung', name: 'Miso Pedas Jagung', price: 39000, cat: ['kuah', 'pedas'], spicy: 2, img: 'img/menu/miso-jagung.webp', signature: true,
    desc: 'Kuah miso pedas, sapi cincang, jamur shiitake, jagung manis, dan tiga potong telur setengah matang.' },
  { id: 'kari-udang', name: 'Ramen Kari Udang', price: 45000, cat: ['kuah'], spicy: 1, img: 'img/menu/kari-udang.webp', signature: true,
    desc: 'Kuah kari Jepang yang kental, udang goreng tepung, kimchi, bayam, dan telur ajitama.' },
  { id: 'tori-paitan', name: 'Tori Paitan Jamur', price: 41000, cat: ['kuah'], spicy: 0, img: 'img/3d/tori-paitan.webp', model: 'tori-paitan',
    desc: 'Kuah ayam putih yang creamy, ayam panggang, jamur, dua telur rebus, ketumbar, dan wijen hitam.' },
  { id: 'sapi-rempah', name: 'Mi Kuah Sapi Rempah', price: 38000, cat: ['kuah', 'pedas'], spicy: 2, img: 'img/menu/sapi-rempah.webp',
    desc: 'Mi beras, sapi rebus rempah, daun ketumbar, cabai rawit segar, dan kacang sangrai.' },
  { id: 'paitan-ayam', name: 'Paitan Chashu Ayam', price: 36000, cat: ['kuah'], spicy: 0, img: 'img/menu/paitan-ayam.webp',
    desc: 'Kuah ayam kental, chashu ayam panggang berlapis lada, telur ajitama, dan daun bawang.' },
  { id: 'shoyu-ajitama', name: 'Shoyu Ajitama', price: 33000, cat: ['kuah'], spicy: 0, img: 'img/3d/shoyu-ajitama.webp', model: 'shoyu-ajitama',
    desc: 'Kuah shoyu kemerahan, chashu ayam, telur ajitama, nori lebar, dan daun bawang.' },
  { id: 'shoyu-nori', name: 'Shoyu Nori', price: 32000, cat: ['kuah', 'pedas'], spicy: 1, img: 'img/menu/shoyu-nori.webp',
    desc: 'Kuah kecap asin bening dengan nori, menma, serpihan cabai, dan telur ajitama.' },
  { id: 'shio-naruto', name: 'Shio Naruto', price: 34000, cat: ['kuah'], spicy: 0, img: 'img/menu/shio-naruto.webp',
    desc: 'Kuah garam yang gurih, chashu ayam, narutomaki, bawang putih goreng, dan daun bawang.' },
  { id: 'shio-bening', name: 'Shio Bening Spesial', price: 37000, cat: ['kuah'], spicy: 0, img: 'img/menu/shio-bening.webp',
    desc: 'Kuah garam jernih, chashu ayam, menma, bawang prei iris, nori remuk, dan dua telur.' },
  { id: 'nabeyaki', name: 'Nabeyaki Ramen', price: 48000, cat: ['kuah'], spicy: 0, img: 'img/menu/nabeyaki.webp', badge: 'Mangkuk panas',
    desc: 'Disajikan mendidih di mangkuk tanah liat: udang tempura, ayam, jamur, wortel, dan telur yang matang di kuah.' },
  { id: 'shoyu-klasik', name: 'Shoyu Klasik', price: 30000, cat: ['kuah'], spicy: 0, img: 'img/menu/shoyu-klasik.webp',
    desc: 'Resep paling sederhana: kuah kecap asin, nori, narutomaki, menma, dan daun bawang.' },
  { id: 'sayur-tomat', name: 'Ramen Sayur Tomat', price: 35000, cat: ['kuah', 'sayur', 'pedas'], spicy: 1, img: 'img/menu/sayur-tomat.webp',
    desc: 'Kuah tomat pedas tanpa daging: teratai goreng, zukini, terong, wortel, tomat ceri, dan telur.' },
  { id: 'hiyashi', name: 'Hiyashi Ramen Dingin', price: 37000, cat: ['dingin'], spicy: null, img: 'img/menu/hiyashi.webp', badge: 'Segar',
    desc: 'Mi dingin di kuah segar berisi es: udang, telur dadar iris, timun, wortel, dan jamur.' },
  { id: 'shin-bowl', name: 'Shin Ramyun Big Bowl', price: 24000, cat: ['instan', 'pedas'], spicy: null, img: 'img/3d/shin-bowl.webp', model: 'shin-bowl',
    desc: 'Ramen instan Korea versi mangkuk besar, kami seduh di tempat. Bisa ditambah topping.' },
  { id: 'shin-cup', name: 'Shin Ramyun Cup', price: 18000, cat: ['instan', 'pedas'], spicy: null, img: 'img/3d/shin-cup.webp', model: 'shin-cup',
    desc: 'Ramen instan Korea dalam cup, diseduh di tempat. Pedasnya pedas khas Korea.' },
];

export const EXTRAS = [
  { id: 'x-telur', name: 'Telur ajitama', price: 6000, group: 'Topping' },
  { id: 'x-chashu', name: 'Chashu ayam (2 iris)', price: 10000, group: 'Topping' },
  { id: 'x-mie', name: 'Tambah mie', price: 8000, group: 'Topping' },
  { id: 'x-nori', name: 'Nori', price: 3000, group: 'Topping' },
  { id: 'x-jagung', name: 'Jagung manis', price: 4000, group: 'Topping' },
  { id: 'x-keju', name: 'Keju leleh', price: 6000, group: 'Topping' },
  { id: 'd-ocha', name: 'Ocha dingin', price: 8000, group: 'Minuman' },
  { id: 'd-lemon', name: 'Lemon tea', price: 10000, group: 'Minuman' },
  { id: 'd-tarik', name: 'Teh tarik', price: 12000, group: 'Minuman' },
  { id: 'd-air', name: 'Air mineral', price: 5000, group: 'Minuman' },
];

export const CATS = [
  { id: 'semua', label: 'Semua' },
  { id: 'kuah', label: 'Berkuah' },
  { id: 'pedas', label: 'Pedas' },
  { id: 'dingin', label: 'Dingin' },
  { id: 'sayur', label: 'Tanpa daging' },
  { id: 'instan', label: 'Instan Korea' },
];

export const SPICY = ['Tidak pedas', 'Sedikit', 'Sedang', 'Pedas', 'Pedas banget', 'Level neraka'];
