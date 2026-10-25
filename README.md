# risetanddevelopment-yota

# TERA - Research & Development Workspace
> **Platform Terpadu Pengelolaan Siklus Hidup Riset, Inovasi Produk, dan Dampak Sosial**

TERA R&D Workspace adalah web application dan Progressive Web App (PWA) modern berbasis **Laravel 12, Inertia.js, Vue 3, dan TailwindCSS** untuk membantu peneliti, inovator, dan pegiat sosial mendokumentasikan proses dari ide awal hingga luaran nyata.

---

## 🌟 Fitur Utama

1. **Ideation & Taman Ide (*Innovation Capture*)**
   - Tangkap ide dengan cepat dengan format terstruktur (rumusan masalah, hipotesis, nilai kebaruan).
   - Filter ide berdasarkan mode (*Research Development*, *Product Development*, *Social Project*).
   - 1-Klik konversi ide menjadi proyek nyata.

2. **Roadmap & Milestone Terstruktur**
   - Fase pengembangan dinamis sesuai mode inisiatif.
   - Milestone dengan *Definition of Done* yang jelas.
   - Checklist tugas harian dengan perhitungan progres otomatis.

3. **Auto-Logbook & Development Journal**
   - Catat kemajuan harian dalam hitungan menit.
   - Lampirkan foto bukti lapangan dan hasil pengujian.
   - Log sistem otomatis saat tugas diselesaikan.
   - Kontrol visibilitas publik/privat per catatan.

4. **Kolaborasi Tim (*Team Workspace & 1-Click Join*)**
   - Bagikan tautan undangan 1-klik (`/projects/join/{token}`).
   - Undang anggota tim melalui email dengan peran spesifik (*Co-Lead, Peneliti/Riset, Spesialis Teknis, Kolaborator, Reviewer Tim Internal*).
   - Akses kolaboratif untuk pengisian tugas, logbook, RAB, dan luaran.

5. **Transparansi RAB & Realisasi Belanja**
   - Rencana Anggaran Biaya (RAB) per kategori (*Alat & Bahan, Operasional, Publikasi & Uji Coba, Jasa & Mitra*).
   - Pencatatan realisasi belanja dengan kalkulasi selisih anggaran (*variance*) otomatis.

6. **Tautan Publik Proyek (*Open Innovation Share*)**
   - Halaman publik instan tanpa mengharuskan reviewer atau mitra membuat akun.
   - Pemilik proyek mengendalikan bagian yang dibuka (Ringkasan, Roadmap, Logbook, RAB, Luaran).
   - Form masukan dan umpan balik bintang dari reviewer ahli atau mitra.

7. **Progressive Web App (PWA) Ready**
   - Dapat dipasang (*install*) langsung di HP Android, iOS, maupun Desktop.
   - Standalone app experience tanpa bar peramban.
   - Offline fallback page saat jaringan internet terputus.

8. **Autentikasi & Keamanan**
   - Login & registrasi aman dengan enkripsi password.
   - Dukungan Login 1-Klik dengan Google OAuth (Socialite).

---

## 🏛️ Inisiator Platform
Platform ini diinisiasi oleh:
* **[PT Yota Inovasi Nusantara (YOIN)](https://yotainovasi.id/)** — Transformasi Digital, Jasa Website & Ekosistem Teknologi
* **[Yota Adiwidya Center (SIYOTA)](https://siyota.org/)** — Riset, Edukasi, dan Pemberdayaan

---

## 🚀 Panduan Instalasi Lokal

```bash
# Clone repository
git clone https://github.com/Trifebri3/risetanddevelopment-yota.git
cd risetanddevelopment-yota

# Install dependensi PHP & Node.js
composer install
npm install

# Salin file environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Migrasi & Seeder Database
php artisan migrate --seed

# Build asset frontend
npm run build

# Jalankan server
php artisan serve
```

---

## 🧪 Pengujian (Automated Tests)

```bash
php artisan test
```

---

## 📄 Lisensi
Hak Cipta &copy; 2026 PT Yota Inovasi Nusantara (YOIN) & Yota Adiwidya Center.
Open-source di bawah lisensi MIT.
