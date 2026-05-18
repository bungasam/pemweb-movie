🍵 CineView
Sistem Review Film & Series (Community Movie Review Platform)

Platform review film dan series berbasis web yang memungkinkan pengguna untuk memberikan ulasan, rating, serta menemukan rekomendasi tontonan terbaik secara mudah dan interaktif.

📋 Deskripsi

CineView adalah platform berbasis web yang dirancang untuk menjadi tempat bagi pengguna dalam mencari, membaca, dan membagikan review film maupun series favorit mereka. Sistem ini membantu pengguna menemukan rekomendasi tontonan berdasarkan rating dan ulasan komunitas.

Melalui CineView, pengguna dapat membuat akun, login, memberikan review, memberi rating, serta melihat rekomendasi film dengan penilaian tertinggi. Semua review akan ditampilkan pada halaman utama sehingga pengguna lain dapat melihat pengalaman dan pendapat komunitas terhadap suatu film atau series.

Selain itu, admin memiliki akses khusus untuk mengelola data film, review pengguna, serta memantau aktivitas platform melalui dashboard admin.

📝 Judul Proyek
Pengembangan Sistem Review Film dan Series Berbasis Web Menggunakan Metode Scrum
🌐 Gambaran Umum Sistem

CineView merupakan platform web untuk review film dan series yang menggabungkan fitur komunitas, sistem rating, dan rekomendasi film dalam satu website. Sistem ini dibuat untuk memudahkan pengguna dalam menemukan tontonan berkualitas sekaligus membagikan pendapat mereka terhadap film yang telah ditonton.

Platform menyediakan fitur login dan registrasi pengguna, manajemen review, rekomendasi film berdasarkan rating tertinggi, pencarian film, serta dashboard admin untuk pengelolaan sistem.

👥 Anggota Kelompok dan Tanggung Jawab
No	Nama Anggota	Role	Tanggung Jawab
1.	Bunga Salma Azzahrah	: Frontend / User Interface	Mendesain tampilan website, halaman landing page, halaman daftar film, form review, serta pengembangan antarmuka pengguna.
2.	Naylarifa Maulida Asri	: Backend / User System	Mengembangkan sistem login, registrasi, session, database user, serta proses CRUD review dan rating.
3.	Gayatri Pradnya Aira Putri :	Admin & Database System	Mengembangkan dashboard admin, manajemen film dan review, struktur database, serta integrasi sistem.

🎓 NIM Anggota Kelompok
Bunga Salma Azzahrah: F1D02410110
Naylarifa Maulida Asri : F1D02410019
Gayatri Pradnya Aira Putri : F1D02410113

🔄 Metodologi
Proyek ini menggunakan metode Scrum.

⚙️ Workflow Scrum
Durasi sprint: 1 minggu per sprint
Review progres: Dilakukan setiap akhir sprint
Pembagian tugas: Setiap anggota fokus pada bagian utama sistem
Tujuan: Mengembangkan sistem secara bertahap agar proses integrasi lebih mudah dan terstruktur

📌 Contoh Alur Sprint
Sprint 1 : Perancangan UI, Desain database, Pembuatan wireframe
Sprint 2 : Sistem login dan registrasi, Struktur session user
Sprint 3 : Landing page dan daftar film, Sistem pencarian film
Sprint 4 : Sistem review dan rating, Penyimpanan review ke database
Sprint 5 : Sistem rekomendasi film, Tampilan detail film
Sprint 6 : Dashboard admin, Manajemen film dan review
Sprint 7 : Testing, Debugging, Integrasi final sistem

💻 Teknologi yang Digunakan
Frontend : HTML, CSS, JavaScript
Backend : PHP
Database : MySQL
Local Server : XAMPP
Version Control : Git / GitHub

🎨 Design System
Color Palette
Primary Matcha : #7D966D
Cream : #F5F0E6
Dark Text : #2D2D2D
White : #FFFFFF
Soft Brown : #B89B7A
Fonts
Logo / Heading : Poppins
Main UI : Poppins, Sans-serif

🗺️ Sitemap
CineView
├── Homepage
│   ├── Navbar
│   │   ├── Beranda
│   │   ├── Explore Film
│   │   ├── Login
│   │   ├── Registrasi
│   │   └── Tentang
│   ├── Banner Utama
│   ├── Daftar Film Populer
│   ├── Rekomendasi Film
│   └── Footer
│
├── Login & Register
│   ├── Login User
│   ├── Registrasi Akun
│   └── Logout
│
├── Halaman Film
│   ├── Poster Film
│   ├── Deskripsi Film
│   ├── Rating
│   ├── Review User
│   ├── Tambah Review
│   ├── Edit Review
│   └── Hapus Review
│
├── Rekomendasi
│   └── Film Rating Tertinggi
│
├── Profil User
│   ├── Username
│   ├── Email
│   ├── Riwayat Review
│   └── Logout
│
└── Admin Dashboard
    ├── Statistik Website
    ├── Kelola Film
    │   ├── Tambah Film
    │   ├── Edit Film
    │   └── Hapus Film
    ├── Kelola Review
    │   ├── Lihat Review
    │   └── Hapus Review
    └── Kelola User
    
🗂️ Struktur Proyek
CineView/
├── index.php
├── login.php
├── register.php
├── logout.php
├── rekomendasi.php
├── tentang.php
├── detail_film.php
├── proses_review.php
├── config.php
├── style.css
├── script.js
│
├── admin/
│   ├── dashboard.php
│   ├── tambah_film.php
│   ├── edit_film.php
│   ├── hapus_film.php
│   ├── kelola_review.php
│   └── hapus_review.php
│
├── user/
│   ├── edit_review.php
│   ├── hapus_review.php
│   └── profile.php
│
├── img/
│   └── default.jpg
│
└── database/
    └── cineview.sql
    
🌐 Alamat Website
http://localhost/cineview/
