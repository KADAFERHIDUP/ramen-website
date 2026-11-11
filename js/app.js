import { SHOP, MENU, EXTRAS, CATS, SPICY } from './data.js';
import { MODELS } from './models.js';

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const rupiah = n => 'Rp ' + n.toLocaleString('id-ID');
const short = n => (n % 1000 ? (n / 1000).toFixed(1).replace('.', ',') : n / 1000) + 'rb';
const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const byId = new Map([...MENU, ...EXTRAS].map(i => [i.id, i]));
const store = {
  get(k, d) { try { const v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch { return d; } },
  set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* penyimpanan tidak tersedia */ } },
};
const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

function openWhatsApp(text) {
  const url = `https://wa.me/${SHOP.whatsapp}?text=${encodeURIComponent(text)}`;
  const w = window.open(url, '_blank', 'noopener');
  if (!w) location.href = url;
}

/* ---------- jam buka ---------- */
const toMin = t => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
const fmtTime = t => t.replace(':', '.');
function nowInShop() {
  const parts = new Intl.DateTimeFormat('en-US', { timeZone: SHOP.timeZone, weekday: 'short', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).formatToParts(new Date());
  const p = Object.fromEntries(parts.map(x => [x.type, x.value]));
  return { day: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].indexOf(p.weekday), min: +p.hour * 60 + +p.minute, date: `${p.year}-${p.month}-${p.day}` };
}
function openState() {
  const n = nowInShop(), h = SHOP.hours[n.day];
  if (h && n.min >= toMin(h[0]) && n.min < toMin(h[1])) return { open: true, text: `Buka · tutup ${fmtTime(h[1])}` };
  if (h && n.min < toMin(h[0])) return { open: false, text: `Tutup · buka ${fmtTime(h[0])}` };
  for (let i = 1; i <= 7; i++) {
    const d = (n.day + i) % 7, hh = SHOP.hours[d];
    if (hh) return { open: false, text: `Tutup · buka ${i === 1 ? 'besok' : DAYS[d]} ${fmtTime(hh[0])}` };
  }
  return { open: false, text: 'Tutup' };
}
function setupHours() {
  const st = openState(), badge = $('#open-badge');
  badge.hidden = false; badge.classList.toggle('closed', !st.open); $('span', badge).textContent = st.text;
  const today = nowInShop().day;
  $('#hours').innerHTML = [1, 2, 3, 4, 5, 6, 0].map(d => {
    const h = SHOP.hours[d];
    return `<li class="${d === today ? 'today' : ''}"><span>${DAYS[d]}${d === today ? ' (hari ini)' : ''}</span><span>${h ? `${fmtTime(h[0])}–${fmtTime(h[1])}` : 'Tutup'}</span></li>`;
  }).join('');
  const std = SHOP.hours[1], fri = SHOP.hours[5];
  $('#foot-hours').innerHTML = `Setiap hari ${fmtTime(std[0])}–${fmtTime(std[1])}<br>Jumat ${fmtTime(fri[0])}–${fmtTime(fri[1])}, setelah salat Jumat`;
  setTimeout(setupHours, 60000);
}

/* ---------- navigasi ---------- */
function setupNav() {
  const nav = $('#nav'), burger = $('#burger'), links = $('#links');
  const onScroll = () => nav.classList.toggle('solid', scrollY > 30);
  addEventListener('scroll', onScroll, { passive: true }); onScroll();
  const close = () => { links.classList.remove('open-menu'); burger.setAttribute('aria-expanded', 'false'); };
  burger.addEventListener('click', () => {
    const open = !links.classList.contains('open-menu');
    links.classList.toggle('open-menu', open); burger.setAttribute('aria-expanded', String(open));
  });
  links.addEventListener('click', e => { if (e.target.closest('a')) close(); });
  addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  const map = new Map($$('a', links).map(a => [a.getAttribute('href').slice(1), a]));
  const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { map.forEach(a => a.classList.remove('on')); map.get(e.target.id)?.classList.add('on'); }
  }), { rootMargin: '-45% 0px -50% 0px' });
  map.forEach((_, id) => { const s = document.getElementById(id); if (s) io.observe(s); });
}

function setupReveal() {
  const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -8% 0px' });
  $$('.reveal').forEach(el => io.observe(el));
}

/* ---------- menu ---------- */
function tagsOf(it) {
  const t = [];
  if (it.badge) t.push(`<span class="tag star">${esc(it.badge)}</span>`);
  if (it.cat.includes('pedas')) t.push('<span class="tag hot">Pedas</span>');
  if (it.cat.includes('dingin')) t.push('<span class="tag">Dingin</span>');
  if (it.model) t.push('<span class="tag d3">3D</span>');
  return t.join('');
}
function renderSignature() {
  $('#sig-grid').innerHTML = MENU.filter(i => i.signature).map(it => `
    <article class="sig reveal">
      <div class="sig-img">
        <span class="price-dot"><span>${short(it.price)}<small>rupiah</small></span></span>
        <img src="${it.img}" alt="${esc(it.name)}" width="720" height="720" loading="lazy">
      </div>
      <h3 class="sig-name">${esc(it.name)}</h3>
      <p>${esc(it.desc)}</p>
      <button class="btn btn-yellow btn-sm" type="button" data-add="${it.id}">+ Tambah ke keranjang</button>
    </article>`).join('');
}
let cat = 'semua';
function renderChips() {
  $('#chips').innerHTML = CATS.map(c => {
    const n = c.id === 'semua' ? MENU.length : MENU.filter(i => i.cat.includes(c.id)).length;
    return `<button class="chip" type="button" role="tab" data-cat="${c.id}" aria-selected="${c.id === cat}">${c.label}<small>${n}</small></button>`;
  }).join('');
}
function renderMenu() {
  const list = cat === 'semua' ? MENU : MENU.filter(i => i.cat.includes(cat));
  $('#menu-grid').innerHTML = list.length ? list.map((it, k) => `
    <article class="item" style="animation-delay:${Math.min(k, 8) * 40}ms">
      <div class="tags">${tagsOf(it)}</div>
      <div class="item-img"><img src="${it.img}" alt="${esc(it.name)}" width="720" height="720" loading="lazy"></div>
      <h3>${esc(it.name)}</h3>
      <p>${esc(it.desc)}</p>
      ${it.model ? `<button class="view3d" type="button" data-view="${it.model}">Lihat dalam 3D</button>` : ''}
      <div class="item-row"><span class="item-price">${rupiah(it.price)}</span><button class="add" type="button" data-add="${it.id}" aria-label="Tambah ${esc(it.name)} ke keranjang">+</button></div>
    </article>`).join('') : '<p class="empty-note">Belum ada menu di kategori ini.</p>';
}
function renderExtras() {
  const groups = [...new Set(EXTRAS.map(e => e.group))];
  $('#extras').innerHTML = groups.map(g => `
    <div class="extra-box"><h3>${g}</h3><ul class="extra-list">${EXTRAS.filter(e => e.group === g).map(e => `
      <li><span>${esc(e.name)}</span><b>${rupiah(e.price)}</b><button class="add" type="button" data-add="${e.id}" aria-label="Tambah ${esc(e.name)}">+</button></li>`).join('')}
    </ul></div>`).join('');
}
function setupMenu() {
  renderSignature(); renderChips(); renderMenu(); renderExtras();
  $('#chips').addEventListener('click', e => {
    const b = e.target.closest('[data-cat]'); if (!b) return;
    cat = b.dataset.cat; renderChips(); renderMenu();
  });
  $('#chips').addEventListener('keydown', e => {
    if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
    const ids = CATS.map(c => c.id), i = ids.indexOf(cat) + (e.key === 'ArrowRight' ? 1 : -1);
    cat = ids[(i + ids.length) % ids.length]; renderChips(); renderMenu(); $(`[data-cat="${cat}"]`).focus();
  });
}

/* ---------- keranjang ---------- */
let cart = store.get('kumo-cart', []).filter(l => byId.has(l.id) && l.qty > 0);
const lineKey = l => `${l.id}|${l.level ?? ''}`;
function saveCart() { store.set('kumo-cart', cart); }
function addToCart(id, level) {
  const it = byId.get(id); if (!it) return;
  const lv = level !== undefined ? level : (it.spicy ?? null);
  const found = cart.find(l => l.id === id && (l.level ?? null) === lv);
  if (found) found.qty++; else cart.push({ id, qty: 1, level: lv });
  saveCart(); renderCart();
  $$('[data-count]').forEach(c => { c.classList.remove('bump'); void c.offsetWidth; c.classList.add('bump'); setTimeout(() => c.classList.remove('bump'), 300); });
  toast(`${it.name} masuk keranjang${lv !== null && lv !== undefined ? ` · pedas level ${lv}` : ''}`);
}
const total = () => cart.reduce((s, l) => s + byId.get(l.id).price * l.qty, 0);
function renderCart() {
  const n = cart.reduce((s, l) => s + l.qty, 0);
  $$('[data-count]').forEach(c => { c.textContent = n; });
  $('#fab-total').textContent = n ? rupiah(total()) : '';
  $('.fab').classList.toggle('show', n > 0);
  $('#cart').classList.toggle('has-items', n > 0);
  $('#cart-total').textContent = rupiah(total());
  $('#cart-list').innerHTML = cart.map((l, i) => {
    const it = byId.get(l.id);
    const pic = it.img ? `<img src="${it.img}" alt="" width="58" height="58">` : `<span class="ph" aria-hidden="true">${esc(it.name[0])}</span>`;
    const lvl = l.level !== null && l.level !== undefined
      ? `<select data-level="${i}" aria-label="Level pedas ${esc(it.name)}">${SPICY.map((s, k) => `<option value="${k}" ${k === l.level ? 'selected' : ''}>Level ${k} · ${s}</option>`).join('')}</select>` : '';
    return `<li class="line">${pic}<div><h3>${esc(it.name)}</h3><div class="sub">${rupiah(it.price)} × ${l.qty} = <b>${rupiah(it.price * l.qty)}</b></div>${lvl}</div>
      <div class="qty"><button type="button" data-dec="${i}" aria-label="Kurangi ${esc(it.name)}">−</button><output aria-live="polite">${l.qty}</output><button type="button" data-inc="${i}" aria-label="Tambah ${esc(it.name)}">+</button></div></li>`;
  }).join('');
}
function mergeCart() {
  const m = new Map();
  cart.forEach(l => { const k = lineKey(l); if (m.has(k)) m.get(k).qty += l.qty; else m.set(k, { ...l }); });
  cart = [...m.values()].filter(l => l.qty > 0);
}
function setupCart() {
  const dlg = $('#cart'), form = $('#cart-form');
  const saved = store.get('kumo-buyer', {});
  if (saved.name) form.name.value = saved.name;
  if (saved.address) form.address.value = saved.address;
  const syncMode = () => {
    const mode = form.mode.value;
    $('.addr', form).hidden = mode !== 'Diantar';
    $('#cart-fine').textContent = mode === 'Diantar' ? 'Ongkir dihitung sesuai jarak dan dikonfirmasi lewat WhatsApp.' : mode === 'Ambil sendiri' ? 'Kami kabari lewat WhatsApp saat pesanan siap diambil.' : 'Pesanan langsung disiapkan saat kamu tiba. Sebut nama pemesan di kasir.';
  };
  form.addEventListener('change', e => { if (e.target.name === 'mode') syncMode(); });
  syncMode();
  const open = () => { if (!dlg.open) dlg.showModal(); };
  $$('[data-open-cart]').forEach(b => b.addEventListener('click', open));
  dlg.addEventListener('click', e => {
    if (e.target === dlg || e.target.closest('[data-close]')) { dlg.close(); }
    const inc = e.target.closest('[data-inc]'), dec = e.target.closest('[data-dec]');
    if (inc) { cart[+inc.dataset.inc].qty++; }
    if (dec) { cart[+dec.dataset.dec].qty--; }
    if (inc || dec) { mergeCart(); saveCart(); renderCart(); }
  });
  dlg.addEventListener('change', e => {
    const s = e.target.closest('[data-level]'); if (!s) return;
    cart[+s.dataset.level].level = +s.value; mergeCart(); saveCart(); renderCart();
  });
  form.addEventListener('submit', e => {
    e.preventDefault();
    const err = $('#cart-error'); err.hidden = true;
    $$('[aria-invalid]', form).forEach(x => x.removeAttribute('aria-invalid'));
    const name = form.name.value.trim(), mode = form.mode.value, address = form.address.value.trim();
    const bad = [];
    if (!cart.length) bad.push('keranjang masih kosong');
    if (!name) { bad.push('isi nama pemesan'); form.name.setAttribute('aria-invalid', 'true'); }
    if (mode === 'Diantar' && address.length < 6) { bad.push('isi alamat antar'); form.address.setAttribute('aria-invalid', 'true'); }
    if (bad.length) { err.textContent = 'Sebelum mengirim: ' + bad.join(', ') + '.'; err.hidden = false; $('[aria-invalid]', form)?.focus(); return; }
    store.set('kumo-buyer', { name, address });
    const lines = cart.map((l, i) => {
      const it = byId.get(l.id);
      const lv = l.level !== null && l.level !== undefined ? ` (pedas level ${l.level})` : '';
      return `${i + 1}. ${it.name} ×${l.qty}${lv} = ${rupiah(it.price * l.qty)}`;
    });
    const msg = [
      `Halo ${SHOP.name}, saya mau pesan:`, '', ...lines, '',
      `Subtotal: ${rupiah(total())}`,
      `Cara terima: ${mode}`,
      `Nama: ${name}`,
      mode === 'Diantar' ? `Alamat: ${address}` : null,
      `Bayar: ${form.pay.value}`,
      form.note.value.trim() ? `Catatan: ${form.note.value.trim()}` : null,
      '', mode === 'Diantar' ? 'Mohon info total dengan ongkir. Terima kasih!' : 'Terima kasih!',
    ].filter(x => x !== null).join('\n');
    openWhatsApp(msg);
    toast('Pesanan dibuka di WhatsApp. Tinggal tekan kirim.');
  });
  document.addEventListener('click', e => {
    const add = e.target.closest('[data-add]');
    if (add) { addToCart(add.dataset.add); add.classList.add('added'); setTimeout(() => add.classList.remove('added'), 500); }
  });
  renderCart();
}

let toastTimer = 0;
function toast(text) {
  const t = $('#toast'); t.textContent = text; t.classList.add('show');
  clearTimeout(toastTimer); toastTimer = setTimeout(() => t.classList.remove('show'), 2600);
}

/* ---------- reservasi ---------- */
function setupReservation() {
  const f = $('#res-form'), today = nowInShop();
  f.date.min = today.date; f.date.value = today.date;
  const max = new Date(); max.setDate(max.getDate() + 60); f.date.max = max.toISOString().slice(0, 10);
  f.people.innerHTML = Array.from({ length: 12 }, (_, i) => `<option value="${i + 1} orang" ${i === 1 ? 'selected' : ''}>${i + 1} orang</option>`).join('') + '<option value="Lebih dari 12 orang">Lebih dari 12</option>';
  const fillTimes = () => {
    const d = new Date(f.date.value + 'T12:00:00'), day = isNaN(d) ? today.day : d.getDay(), h = SHOP.hours[day];
    const opts = [];
    if (h) for (let m = toMin(h[0]); m <= toMin(h[1]) - 60; m += 30) {
      if (f.date.value === today.date && m < today.min + 30) continue;
      const t = `${String(Math.floor(m / 60)).padStart(2, '0')}:${String(m % 60).padStart(2, '0')}`;
      opts.push(`<option value="${t}">${fmtTime(t)}</option>`);
    }
    f.time.innerHTML = opts.length ? opts.join('') : '<option value="">Penuh/tutup, pilih tanggal lain</option>';
  };
  f.date.addEventListener('change', fillTimes); fillTimes();
  if (!f.time.value) {
    const t = new Date(today.date + 'T12:00:00'); t.setDate(t.getDate() + 1);
    f.date.value = `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-${String(t.getDate()).padStart(2, '0')}`; fillTimes();
  }
  f.addEventListener('submit', e => {
    e.preventDefault();
    const err = $('#res-error'); err.hidden = true;
    $$('[aria-invalid]', f).forEach(x => x.removeAttribute('aria-invalid'));
    const bad = [];
    if (!f.name.value.trim()) { bad.push('nama'); f.name.setAttribute('aria-invalid', 'true'); }
    if (f.phone.value.replace(/\D/g, '').length < 9) { bad.push('nomor WhatsApp yang benar'); f.phone.setAttribute('aria-invalid', 'true'); }
    if (!f.date.value || f.date.value < today.date) { bad.push('tanggal'); f.date.setAttribute('aria-invalid', 'true'); }
    if (!f.time.value) { bad.push('jam'); }
    if (bad.length) { err.textContent = 'Lengkapi dulu: ' + bad.join(', ') + '.'; err.hidden = false; $('[aria-invalid]', f)?.focus(); return; }
    const d = new Date(f.date.value + 'T12:00:00');
    const dateText = `${DAYS[d.getDay()]}, ${d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}`;
    const msg = [
      `Halo ${SHOP.name}, saya mau reservasi meja:`, '',
      `Nama: ${f.name.value.trim()}`,
      `No. WhatsApp: ${f.phone.value.trim()}`,
      `Tanggal: ${dateText}`,
      `Jam: ${fmtTime(f.time.value)}`,
      `Jumlah: ${f.people.value}`,
      `Tempat: ${f.area.value}`,
      f.note.value.trim() ? `Catatan: ${f.note.value.trim()}` : null,
      '', 'Mohon konfirmasinya. Terima kasih!',
    ].filter(x => x !== null).join('\n');
    openWhatsApp(msg);
    toast('Reservasi dibuka di WhatsApp. Tinggal tekan kirim.');
  });
}

/* ---------- info & footer ---------- */
function setupInfo() {
  const wa = `https://wa.me/${SHOP.whatsapp}`;
  $('#wa-link').href = wa; $('#wa-link').textContent = `WhatsApp ${SHOP.whatsappLabel}`;
  $('#loc-text').innerHTML = SHOP.address ? `${esc(SHOP.address)}<br>${esc(SHOP.city)}` : `${esc(SHOP.city)}. Tanya rute ke kedai lewat WhatsApp, kami kirim titik lokasinya.`;
  if (SHOP.mapsUrl) $('#loc-text').insertAdjacentHTML('beforeend', ` <a href="${esc(SHOP.mapsUrl)}" target="_blank" rel="noopener">Buka di Google Maps</a>`);
  $('#foot-contact').innerHTML = [
    `<li><a href="${wa}" target="_blank" rel="noopener">WhatsApp ${SHOP.whatsappLabel}</a></li>`,
    SHOP.instagram ? `<li><a href="https://instagram.com/${esc(SHOP.instagram)}" target="_blank" rel="noopener">Instagram @${esc(SHOP.instagram)}</a></li>` : '',
    `<li>${esc(SHOP.city)}</li>`,
  ].join('');
  $('#credits').innerHTML = Object.values(MODELS).map(m => `<a href="${m.credit.url}" target="_blank" rel="noopener">“${esc(m.credit.title)}”</a> oleh <a href="${m.credit.authorUrl}" target="_blank" rel="noopener">${esc(m.credit.author)}</a>`).join(', ');
  $('#year').textContent = new Date().getFullYear();
}

/* ---------- 3D ---------- */
const VIEW_ITEMS = MENU.filter(i => i.model);
let viewer = null, current = VIEW_ITEMS[0].model, loadToken = 0;
function renderTabs() {
  $('#model-tabs').innerHTML = VIEW_ITEMS.map(it => `
    <button class="tab" type="button" role="tab" data-model="${it.model}" aria-selected="${it.model === current}">
      <img src="${it.img}" alt="" width="46" height="46" loading="lazy"><span>${esc(it.name)}</span><small>${short(it.price)}</small>
    </button>`).join('');
  const it = VIEW_ITEMS.find(i => i.model === current), c = MODELS[current].credit;
  $('#viewer-info').innerHTML = `
    <h3>${esc(it.name)}</h3><p>${esc(it.desc)}</p>
    <div class="item-row"><span class="item-price">${rupiah(it.price)}</span><button class="btn btn-yellow btn-sm" type="button" data-add="${it.id}">+ Tambah ke keranjang</button></div>
    <p class="credit">Model 3D: <a href="${c.url}" target="_blank" rel="noopener">${esc(c.title)}</a> oleh <a href="${c.authorUrl}" target="_blank" rel="noopener">${esc(c.author)}</a> (CC BY 4.0)</p>`;
  $('#stage-img').src = it.img; $('#stage-img').alt = it.name;
}
async function selectModel(id, scene) {
  current = id; renderTabs();
  if (!scene) return;
  const token = ++loadToken, stage = $('#stage'), loader = $('#loader');
  stage.classList.add('loading');
  const slow = setTimeout(() => { if (token === loadToken) loader.hidden = false; }, 150);
  try {
    const m = await scene.getModel(id, p => loader.style.setProperty('--p', Math.round(p * 100) + '%'));
    if (token !== loadToken) return;
    viewer.setModel(m, MODELS[id].pitch, MODELS[id].yaw);
    stage.classList.remove('fallback'); stage.classList.add('live');
  } catch (err) {
    console.error(err);
    if (token === loadToken) stage.classList.add('fallback');
  } finally {
    clearTimeout(slow);
    if (token === loadToken) { loader.hidden = true; stage.classList.remove('loading'); }
  }
}
function setupViewerUI(scene) {
  renderTabs();
  $('#model-tabs').addEventListener('click', e => { const b = e.target.closest('[data-model]'); if (b && b.dataset.model !== current) selectModel(b.dataset.model, scene); });
  document.addEventListener('click', e => {
    const v = e.target.closest('[data-view]'); if (!v) return;
    $('#tiga-d').scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    if (v.dataset.view !== current) selectModel(v.dataset.view, scene);
  });
}

async function start3D() {
  const { webglOK, createScene } = await import('./scene3d.js');
  if (!webglOK()) throw new Error('WebGL tidak tersedia');
  const scene = createScene($('#gl'));
  document.body.classList.add('has3d');
  const heroSpot = $('#hero-spot'), whySpot = $('#why-spot'), stage = $('#stage'), vSpot = $('#viewer-spot');

  // mangkuk utama: tampil di hero, lalu muncul lagi di bagian "Kenapa"
  const hero = scene.addActor({
    spin: 0.28, steam: true, parallax: 0.22,
    follow() {
      let best = null, bv = -Infinity;
      for (const [key, el, fill, pitch] of [['hero', heroSpot, 1, 0.42], ['why', whySpot, 0.92, 0.55]]) {
        const r = el.getBoundingClientRect(); if (!r.width) continue;
        const v = (Math.min(r.bottom, innerHeight) - Math.max(r.top, 0)) / r.height;
        if (v > bv) { bv = v; best = { key, rect: r, fill, pitch }; }
      }
      // tetap menempel ke spot terakhir saat keluar layar, supaya ikut tergulir lalu memudar
      if (!best) return null;
      best.op = bv > 0.25 ? 1 : 0;
      return best;
    },
  });
  // penampil 3D: digambar hanya di dalam kotak panggung
  viewer = scene.addActor({
    spin: 0.35,
    follow() {
      const r = stage.getBoundingClientRect();
      if (r.bottom < 0 || r.top > innerHeight) return null;
      return { key: 'viewer', rect: vSpot.getBoundingClientRect(), fill: 0.78 };
    },
    scissor: () => stage.getBoundingClientRect(),
  });
  setupDrag(stage, viewer);
  setupViewerUI(scene);

  const m = await scene.getModel('ramen-spesial');
  hero.setModel(m, 0.42, 0.5);
  document.body.classList.add('ready3d');
  selectModel(current, scene);
}

function setupDrag(stage, a) {
  let down = null, idle = 0;
  const hint = $('#hint');
  const rest = () => { clearTimeout(idle); a.spin = 0; idle = setTimeout(() => { a.spin = 0.35; }, 2500); hint.classList.add('gone'); };
  stage.addEventListener('pointerdown', e => {
    down = { x: e.clientX, y: e.clientY, id: e.pointerId }; stage.setPointerCapture(e.pointerId); stage.classList.add('drag'); rest();
  });
  stage.addEventListener('pointermove', e => {
    if (!down || e.pointerId !== down.id) return;
    const dx = e.clientX - down.x, dy = e.clientY - down.y; down.x = e.clientX; down.y = e.clientY;
    a.yawT += dx * 0.012; a.pitchT = Math.max(-0.1, Math.min(1.3, a.pitchT + dy * 0.008)); rest();
  });
  const up = () => { down = null; stage.classList.remove('drag'); };
  stage.addEventListener('pointerup', up); stage.addEventListener('pointercancel', up);
  stage.addEventListener('keydown', e => {
    const k = { ArrowLeft: [-0.3, 0], ArrowRight: [0.3, 0], ArrowUp: [0, -0.15], ArrowDown: [0, 0.15] }[e.key];
    if (!k) return; e.preventDefault();
    a.yawT += k[0]; a.pitchT = Math.max(-0.1, Math.min(1.3, a.pitchT + k[1])); rest();
  });
}

/* judul hero dikecilkan otomatis kalau baris tengahnya lebih lebar dari layar (font tiap perangkat beda) */
function fitHero() {
  const stage = $('.hero-stage'), line = $('.hero-title .split');
  stage.style.removeProperty('--fs');
  if (getComputedStyle(line).flexDirection === 'column') return;
  const fs = parseFloat(getComputedStyle($('.hero-title')).fontSize), w = line.scrollWidth, max = innerWidth * 0.94;
  if (w > max) stage.style.setProperty('--fs', Math.floor(fs * max / w) + 'px');
}

/* ---------- mulai ---------- */
fitHero();
addEventListener('resize', fitHero);
document.fonts?.ready.then(fitHero);
setupNav();
setupHours();
setupMenu();
setupCart();
setupReservation();
setupInfo();
setupReveal();
start3D().catch(err => {
  console.warn('3D dimatikan:', err.message);
  document.body.classList.add('no3d');
  $('#hint').hidden = true;
  setupViewerUI(null);
});
