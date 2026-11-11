// Daftar model 3D dan penyesuaian tampilannya.
// Semua model berlisensi CC BY 4.0 dari Sketchfab; nama pembuat wajib dicantumkan (lihat footer).
import * as THREE from 'three';
import { loadGLB } from './glb.js';

export const MODELS = {
  'ramen-spesial': {
    url: 'models/ramen-spesial.glb',
    pitch: 0.42, yaw: 0.5,
    centerOn: ['Seriy', 'Gold', 'yzor'],   // pusatkan pada mangkuknya, bukan pada mie yang terangkat
    credit: { title: 'Ramen', author: 'BlackBox613', url: 'https://sketchfab.com/3d-models/ramen-4be3c4e905434f708963fee985931038', authorUrl: 'https://sketchfab.com/BlackBox613' },
    tweak(mat) {
      // kuah aslinya hampir bening; dibuat lebih pekat supaya terlihat seperti kuah pedas
      if (mat.name === 'bylion') return new THREE.MeshStandardMaterial({ name: 'kuah', color: 0x7a2208, roughness: 0.3, metalness: 0, envMapIntensity: 0.35, side: THREE.DoubleSide });
      if (mat.name === 'miaso') { mat.color.setRGB(0.9, 0.72, 0.62); }
      return mat;
    },
  },
  'tori-paitan': {
    url: 'models/tori-paitan.glb',
    pitch: 0.62, yaw: 0.4,
    credit: { title: '3D Ramen', author: 'PaulineRL', url: 'https://sketchfab.com/3d-models/3d-ramen-e912b8f2da144a3fb997a3802df64806', authorUrl: 'https://sketchfab.com/PaulineRL' },
  },
  'shoyu-ajitama': {
    url: 'models/shoyu-ajitama.glb',
    pitch: 0.55, yaw: 0.5,
    credit: { title: 'Stylized Ramen Bowl 3D Model [Rapid Assets]', author: 'RapidAssets', url: 'https://sketchfab.com/3d-models/stylized-ramen-bowl-3d-model-rapid-assets-f6e2e0ceed104655ba9d44a44d51d66b', authorUrl: 'https://sketchfab.com/RapidAssets' },
  },
  'shin-cup': {
    url: 'models/shin-cup.glb',
    pitch: 0.2, yaw: 0.5,
    credit: { title: 'HGU_Korean Cup Noodle Shin Ramen', author: 's_sugie', url: 'https://sketchfab.com/3d-models/hgu-korean-cup-noodle-shin-ramen-5c00fe74ee4943dc985471d16c95f9e5', authorUrl: 'https://sketchfab.com/suzy_jpn' },
  },
  'shin-bowl': {
    url: 'models/shin-bowl.glb',
    pitch: 0.42, yaw: 0.6,
    credit: { title: 'Ramen', author: 'HyperPickle', url: 'https://sketchfab.com/3d-models/ramen-18979b3689f74806925258b42b66c319', authorUrl: 'https://sketchfab.com/HyperPickle' },
  },
};

const cache = new Map();

// Memuat model sekali; hasilnya dinormalkan: titik tengah di (0,0,0), ukuran terbesar = 1.
export function getModel(id, onProgress) {
  if (!cache.has(id)) {
    const def = MODELS[id];
    cache.set(id, loadGLB(def.url, { onProgress }).then(raw => {
      if (def.tweak) {
        const seen = new Map();
        raw.traverse(o => {
          if (!o.isMesh) return;
          o.userData.mat = o.material.name;
          if (!seen.has(o.material)) seen.set(o.material, def.tweak(o.material));
          o.material = seen.get(o.material);
        });
      }
      const box = new THREE.Box3().setFromObject(raw);
      const size = box.getSize(new THREE.Vector3()), center = box.getCenter(new THREE.Vector3());
      if (def.centerOn) {
        const part = new THREE.Box3();
        raw.updateMatrixWorld(true);
        raw.traverse(o => { if (o.isMesh && def.centerOn.includes(o.userData.mat || o.material.name)) part.expandByObject(o); });
        if (!part.isEmpty()) { const c = part.getCenter(new THREE.Vector3()); center.x = c.x; center.z = c.z; }
      }
      const k = 1 / Math.max(size.x, size.y, size.z);
      raw.scale.setScalar(k);
      raw.position.copy(center).multiplyScalar(-k);
      const holder = new THREE.Group();
      holder.add(raw);
      holder.userData.size = size.multiplyScalar(k);
      return holder;
    }));
  }
  return cache.get(id).then(m => m.clone());
}
