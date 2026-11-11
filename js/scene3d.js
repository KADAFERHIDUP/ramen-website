// Satu kanvas WebGL tetap di atas halaman. Setiap "aktor" (model 3D) mengikuti elemen
// penanda di halaman (spot): posisinya dihitung dari letak elemen itu di layar.
import * as THREE from 'three';
import { RoomEnvironment } from '../vendor/RoomEnvironment.js';
import { getModel } from './models.js';

export function webglOK() {
  try { const c = document.createElement('canvas'); return !!(c.getContext('webgl2') || c.getContext('webgl')); } catch { return false; }
}

export function createScene(canvas) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(devicePixelRatio || 1, 1.75));
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.setClearColor(0x000000, 0);
  renderer.autoClear = false;

  const scene = new THREE.Scene();
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
  scene.environmentIntensity = 0.9;
  const key = new THREE.DirectionalLight(0xfff0d8, 2.3); key.position.set(3, 6, 5); scene.add(key);
  const rim = new THREE.DirectionalLight(0xbfe9d8, 1.1); rim.position.set(-5, 2, -4); scene.add(rim);

  const camera = new THREE.PerspectiveCamera(30, 1, 0.1, 100);
  camera.position.set(0, 0, 10);
  const visH = () => 2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * camera.position.z;
  function resize() {
    renderer.setSize(innerWidth, innerHeight, false);
    camera.aspect = innerWidth / innerHeight; camera.updateProjectionMatrix();
  }
  addEventListener('resize', resize);
  resize();

  const calm = matchMedia('(prefers-reduced-motion: reduce)');
  let mx = 0, my = 0;
  addEventListener('pointermove', e => { mx = e.clientX / innerWidth - 0.5; my = e.clientY / innerHeight - 0.5; }, { passive: true });

  const actors = [];

  // uap tipis di atas mangkuk (sprite lembut yang naik dan memudar)
  function steamTexture() {
    const c = document.createElement('canvas'); c.width = c.height = 64;
    const g = c.getContext('2d'), gr = g.createRadialGradient(32, 32, 0, 32, 32, 32);
    gr.addColorStop(0, 'rgba(255,255,255,.55)'); gr.addColorStop(1, 'rgba(255,255,255,0)');
    g.fillStyle = gr; g.fillRect(0, 0, 64, 64);
    const t = new THREE.CanvasTexture(c); t.colorSpace = THREE.SRGBColorSpace; return t;
  }
  const steamTex = steamTexture();

  function addActor({ follow, spin = 0, steam = false, scissor = null, parallax = 0 }) {
    const group = new THREE.Group(), tilt = new THREE.Group(), turn = new THREE.Group();
    group.add(tilt); tilt.add(turn); group.visible = false; scene.add(group);
    const a = {
      group, tilt, turn, follow, spin, scissor, parallax, model: null, key: null,
      cur: { x: 0, y: 0, s: 0.001, op: 0 }, yaw: 0, pitch: 0.4, yawT: 0, pitchT: 0.4, pop: 1,
      puffs: [],
      setModel(m, pitch, yaw) {
        if (a.model) turn.remove(a.model);
        a.model = m; turn.add(m);
        a.pitchT = pitch; a.yawT = a.yaw = yaw; a.pitch = pitch;
        a.pop = 0;
      },
    };
    if (steam) {
      for (let i = 0; i < 7; i++) {
        const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: steamTex, transparent: true, depthWrite: false, opacity: 0 }));
        s.userData.phase = i / 7; s.userData.x = (Math.random() - 0.5) * 0.35;
        group.add(s); a.puffs.push(s);
      }
    }
    actors.push(a);
    return a;
  }

  // ubah kotak elemen di layar menjadi posisi dan ukuran di ruang 3D
  function place(rect, fill) {
    const u = visH() / innerHeight;
    return { x: (rect.left + rect.width / 2 - innerWidth / 2) * u, y: -(rect.top + rect.height / 2 - innerHeight / 2) * u, s: rect.width * u * fill };
  }

  let last = performance.now(), raf = 0, running = true;
  function frame(now) {
    raf = requestAnimationFrame(frame);
    const dt = Math.min(0.05, (now - last) / 1000); last = now;
    if (!running) return;
    const kMove = calm.matches ? 1 : 1 - Math.exp(-dt * 16);
    const kFade = calm.matches ? 1 : 1 - Math.exp(-dt * 5);
    let any = false;
    for (const a of actors) {
      const f = a.follow();
      if (f && f.rect && a.model) {
        const p = place(f.rect, f.fill ?? 1);
        if (f.key !== a.key && a.cur.op < 0.06) { a.cur.x = p.x; a.cur.y = p.y; a.cur.s = p.s; }
        a.key = f.key;
        a.cur.x += (p.x - a.cur.x) * kMove; a.cur.y += (p.y - a.cur.y) * kMove; a.cur.s += (p.s - a.cur.s) * kMove;
        a.cur.op += ((f.op ?? 1) - a.cur.op) * kFade;
        if (f.pitch !== undefined) a.pitchT = f.pitch;
      } else {
        a.cur.op += (0 - a.cur.op) * kFade;
      }
      a.pop += (1 - a.pop) * (calm.matches ? 1 : 1 - Math.exp(-dt * 4));
      if (a.spin && !calm.matches) a.yawT += a.spin * dt;
      a.yaw += (a.yawT - a.yaw) * (1 - Math.exp(-dt * 8));
      a.pitch += (a.pitchT - a.pitch) * (1 - Math.exp(-dt * 8));
      const vis = a.cur.op > 0.01 && a.model;
      a.group.visible = !!vis;
      if (!vis) continue;
      any = true;
      const ease = 1 - Math.pow(1 - Math.min(a.cur.op, 1), 3);
      const pop = 0.75 + 0.25 * (1 - Math.pow(1 - a.pop, 3));
      const bob = calm.matches ? 0 : Math.sin(now / 1000 * 1.3) * 0.05 * a.cur.s;
      a.group.position.set(a.cur.x, a.cur.y + bob, 0);
      a.group.scale.setScalar(Math.max(a.cur.s * ease * pop, 0.0001));
      a.tilt.rotation.set(a.pitch + my * a.parallax, mx * a.parallax * 1.6, 0);
      a.turn.rotation.y = a.yaw;
      a.puffs.forEach(s => {
        const t = ((now / 1000) * 0.22 + s.userData.phase) % 1;
        s.position.set(s.userData.x + Math.sin(t * 6 + s.userData.phase * 9) * 0.06, 0.15 + t * 0.75, 0.1);
        s.scale.setScalar(0.18 + t * 0.45);
        s.material.opacity = Math.sin(t * Math.PI) * 0.32 * ease;
      });
    }
    renderer.clear();
    if (!any) return;
    // aktor tanpa batas digambar dulu, lalu aktor yang dibatasi kotaknya (penampil 3D)
    for (const a of actors) {
      if (!a.group.visible) continue;
      actors.forEach(b => { b.group.userData.v = b.group.visible; b.group.visible = b === a; });
      if (a.scissor) {
        const r = a.scissor(), dpr = renderer.getPixelRatio();
        renderer.setScissorTest(true);
        renderer.setScissor(r.left, innerHeight - r.bottom, r.width, r.height);
      }
      renderer.render(scene, camera);
      renderer.setScissorTest(false);
      actors.forEach(b => { b.group.visible = b.group.userData.v; });
    }
  }
  raf = requestAnimationFrame(frame);
  document.addEventListener('visibilitychange', () => { running = !document.hidden; last = performance.now(); });

  return { addActor, getModel, renderer };
}
