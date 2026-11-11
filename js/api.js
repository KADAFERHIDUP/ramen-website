/* Penghubung ke backend CodeIgniter (opsional).
 * Kalau halaman dibuka dari server backend, menu dan harga diambil dari database,
 * pesanan dan reservasi disimpan dengan kode, lalu pembeli konfirmasi lewat WhatsApp.
 * Kalau tidak ada backend (GitHub Pages, Live Server), website tetap jalan: pesanan langsung lewat WhatsApp.
 * Untuk memakai backend di alamat lain, tulis window.KUMO_API = 'https://domain-kamu/api' sebelum js/app.js. */
let base = null;

async function probe(url) {
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), 2500);
  try {
    const r = await fetch(url + '/health', { signal: ctrl.signal, cache: 'no-store' });
    if (!r.ok) return false;
    const j = await r.json();
    return !!(j && j.ok === true);
  } catch { return false; } finally { clearTimeout(t); }
}

export async function connect() {
  if (base !== null) return base;
  const want = window.KUMO_API ?? 'auto';
  if (want === 'auto') {
    const auto = new URL('api', location.href).href.replace(/\/$/, '');
    base = location.protocol.startsWith('http') && await probe(auto) ? auto : '';
  } else {
    const url = String(want).replace(/\/$/, '');
    base = url && await probe(url) ? url : '';
  }
  return base;
}
export const online = () => !!base;

async function call(path, opts = {}) {
  const r = await fetch(base + path, { headers: { Accept: 'application/json', ...(opts.body ? { 'Content-Type': 'application/json' } : {}) }, ...opts });
  let j = null;
  try { j = await r.json(); } catch { /* bukan JSON */ }
  if (!r.ok) {
    const e = new Error((j && j.message) || `Server menjawab ${r.status}`);
    e.status = r.status; e.fields = (j && j.errors) || {};
    throw e;
  }
  return j;
}
export const menu = async () => (await call('/menu')).data;
export const order = data => call('/orders', { method: 'POST', body: JSON.stringify(data) });
export const reserve = data => call('/reservations', { method: 'POST', body: JSON.stringify(data) });
