# NusaCare AI — Sistem Triase Preventif Keluarga & Radar Kesehatan Masyarakat

> **Kompetisi Website Nasional IT RUSH 2026**  
> **Subtema:** Kesehatan Masyarakat  
> **Nama Ketua Tim:** Pendri Mikola
> **Live Website:** https://nusacare.upahku.my.id/  

---

## 1. Latar Belakang & Solusi
**NusaCare AI** adalah aplikasi web berbasis kecerdasan buatan (*Multimodal AI*) yang menggeser paradigma layanan kesehatan masyarakat dari sekadar penanganan kuratif (saat sakit) menuju upaya **pencegahan dini yang terpadu**.

Platform ini menghubungkan pemantauan kesehatan keluarga secara mandiri langsung dengan fasilitas kesehatan tingkat pertama (**Puskesmas**) melalui **10 halaman terpadu** yang dibagi ke dalam **3 zona akses** (Zona Publik & Autentikasi, Zona Portal Warga/Keluarga, dan Zona Portal Tenaga Medis).

---

## 2. Enam Fitur Inti Berbasis AI & Pemantauan
1. **Multimodal NutriScan AI (`/scanner`)**  
   Pemindaian foto kemasan makanan atau menu harian untuk menganalisis kandungan gula, natrium (garam), serta kalori, sekaligus mengevaluasi tingkat keamanannya berdasarkan profil medis personal pengguna (*Umum, Diabetes, Hipertensi, atau Ibu Hamil*).
2. **Smart Symptom Triage & Maternal Care AI (`/scanner`)**  
   Skrining keluhan fisik dan tanda bahaya kehamilan dalam bahasa sehari-hari untuk menentukan tingkat urgensi (**Hijau / Kuning / Merah**), panduan pertolongan pertama, rekomendasi poli tujuan, serta penerbitan **Tiket Rujukan Digital** otomatis.
3. **AI Program Kesehatan & Smart Reminder (`/wellness-plan`)**  
   Input target kesehatan mandiri (program 7–30 hari) yang dipecah otomatis oleh AI menjadi timeline kegiatan harian bertahap lengkap dengan pengingat in-app dan fitur ekspor jadwal ke kalender perangkat (`.ics`).
4. **AI Wellness & Meal Planner (`/wellness-plan`)**  
   Penyusunan jadwal menu makan sehat berbasis pangan lokal dan program olahraga yang disesuaikan dengan kondisi medis serta ketersediaan waktu pengguna.
5. **Health & Mind Tracker (`/tracker`)**  
   Pencatatan harian tanda vital (*berat badan, tekanan darah, gula darah, durasi tidur, konsumsi air*) dan skor kondisi mental/stres, dilengkapi grafik tren 7–30 hari serta evaluasi otomatis dari AI.
6. **Community Health Radar & Portal Rujukan Medis (`/radar-riwayat` & `/medis/dashboard`)**  
   Peta interaktif sebaran kewaspadaan penyakit musiman (DBD, ISPA, diare) per kelurahan yang terhubung langsung dengan antrean triase rujukan pada dashboard tenaga medis/Puskesmas.

---

## 3. Akses Akun Demo (Untuk Evaluasi Dewan Juri)
Aplikasi menyediakan fitur **1-Click Demo Login** di halaman utama (`/`) maupun halaman masuk (`/login`) sehingga juri dapat langsung menguji kedua peran tanpa perlu registrasi manual:

| Peran Pengguna | Cara Akses | Halaman Tujuan Utama |
| :--- | :--- | :--- |
| **Warga / Keluarga** | Klik tombol **"Coba Demo Warga (1-Klik)"** | `/dashboard`, `/scanner`, `/wellness-plan`, `/tracker`, `/radar-riwayat` |
| **Tenaga Medis / Puskesmas** | Klik tombol **"Demo Portal Tenaga Medis"** | `/medis/dashboard`, `/medis/pasien/{id}` |

---

## 4. Arsitektur Sistem & Tech Stack
- **Arsitektur:** Monolitik Full-Stack berbasis pola **Model-View-Controller (MVC)** dengan pemisahan kontrol akses berbasis peran (*Role-Based Access Control*: `warga` dan `medis`).
- **Backend:** Laravel Framework (PHP 8.4)
- **AI Engine:** Google Gemini API (`gemini-2.5-flash`) via HTTP REST API dengan dukungan pemrosesan **Multimodal (Teks + Gambar Base64)** dan **Structured JSON Output**.
- **Frontend & Visualisasi:** Blade Templating Engine, Tailwind CSS, Chart.js (grafik tren vital & mental), dan Leaflet.js (peta interaktif radar wabah).
- **Database Relasional:** MySQL / PostgreSQL dengan 4 tabel utama:
  - `users` (identitas akun, peran akses, kategori medis personal, dan kelurahan domisili)
  - `health_screenings` (riwayat pemindaian NutriScan & triase gejala, skor risiko, output JSON AI, serta kode tiket rujukan)
  - `wellness_programs` (target kesehatan mandiri, durasi program, timeline harian, dan jadwal pengingat)
  - `health_trackers` (log harian berat badan, tekanan darah, gula darah, tingkat stres, dan catatan mental)

### Struktur Modul Kode Utama
```text
├── app/
│   ├── Http/Controllers/
│   │   ├── AiHubController.php        # Pemrosesan NutriScan, Smart Triage, & Wellness Generator (.ics)
│   │   └── DashboardController.php    # Agregasi skor kesehatan, grafik tren, radar wabah, & antrean medis
│   └── Services/
│       └── GeminiHealthService.php    # Layanan terpusat integrasi Google Gemini Multimodal REST API
├── resources/views/
│   ├── layouts/app.blade.php          # Kerangka utama UI (Palet Deep Teal & Cool Alabaster)
│   ├── landing.blade.php              # Halaman utama publik & 1-Click Demo Login
│   └── ...                            # 9 halaman terpadu Zona Auth, Portal Warga, & Portal Medis
└── routes/web.php                     # Pengaturan routing 3 zona akses aplikasi
```

---

## 5. Deklarasi Library, Aset Open-Source & AI Coding Assistant
Memenuhi ketentuan Bab V Poin 5 Buku Pedoman IT RUSH 2026, seluruh komponen pihak ketiga dan asisten AI yang digunakan dalam pengembangan proyek ini adalah sebagai berikut:

| Kategori | Nama Komponen / Layanan | Tujuan Penggunaan |
| :--- | :--- | :--- |
| **Framework & HTTP Client** | Laravel Framework & Guzzle HTTP | Kerangka kerja backend MVC, autentikasi, dan komunikasi REST API ke Gemini. |
| **UI & Styling** | Tailwind CSS & Google Fonts (*Plus Jakarta Sans*) | Tata letak antarmuka responsif dan tipografi medis modern. |
| **Visualisasi Data & Peta** | Chart.js & Leaflet.js (OpenStreetMap) | Grafik perkembangan tanda vital/mental dan peta interaktif radar wabah kelurahan. |
| **Aset Ikon** | Heroicons (SVG Open-Source) | Ikon navigasi dan indikator fitur antarmuka. |
| **Mesin AI Aplikasi** | Google Gemini API (`gemini-2.5-flash`) | Pemrosesan analisis gizi foto, skrining gejala multimodal, dan generator program sehat. |
| **AI Coding Assistant** | Google Gemini | Membantu penataan struktur kode, validasi debugging, serta penyusunan dokumentasi teknis dan `README.md`. |

---

## 6. Panduan Instalasi Lokal
1. Clone repository ini:
   ```bash
   git clone [Isi URL Repository GitHub]
   cd nusacare-ai
   ```
2. Pasang dependensi PHP dan salin konfigurasi environment:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
3. Atur koneksi database dan masukkan kunci API Gemini pada file `.env`:
   ```env
   DB_DATABASE=nusacare_ai
   DB_USERNAME=root
   DB_PASSWORD=

   GEMINI_API_KEY=your_google_gemini_api_key
   ```
4. Jalankan migrasi database dan server lokal:
   ```bash
   php artisan migrate --seed
   php artisan serve
   ```
