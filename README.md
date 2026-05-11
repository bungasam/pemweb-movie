# 🍵 CineView
### Sistem Review Film & Series *(Community Movie Review Platform)*

Platform review film berbasis web yang memungkinkan pengguna untuk memberikan ulasan dan rating pada film & series favorit mereka secara mudah dan transparan.

---

## 📋 Deskripsi

**CineView** adalah platform review film dan series yang terinspirasi dari kehangatan secangkir matcha latte. Warga bisa mendaftar, login, lalu langsung memberikan review dan rating pada film yang tersedia. Setiap review akan tampil di halaman utama dan berkontribusi pada sistem rekomendasi berbasis rating tertinggi.

Admin dapat mengelola data film dan review melalui dashboard khusus, sementara user biasa cukup login dan mulai mereview film favorit mereka.

---

## 🗺️ Menu Utama

```
USER
  - Landing Page (index.php)
  - Sign Up / Registrasi
  - Login
      - Melihat Daftar Film
      - Memberikan Review & Rating
      - Melihat Rekomendasi Film
      - Tentang Platform

ADMIN
  - Login
      - Dashboard Statistik (Total Film, User, Review)
      - Manajemen Film (Tambah, Edit, Hapus)
      - Manajemen Review (Hapus)
```

---

## 💻 Teknologi

`HTML` `CSS` `JavaScript` `PHP` `MySQL` `Apache`

---

## 🗂️ Struktur Proyek

```
CineView/
├── index.php                  # Landing page & daftar film
├── login.php                  # Login user
├── register.php               # Registrasi akun baru
├── logout.php                 # Logout & hapus session
├── rekomendasi.php            # Film dengan rating tertinggi
├── tentang.php                # Tentang platform
├── proses_review.php          # Proses simpan review
├── koneksi.php                # Konfigurasi koneksi database
├── script.js                  # Search & modal review
├── style.css                  # Tampilan keseluruhan
│
├── admin/                     # Halaman khusus admin
│   ├── dashboard.php          # Statistik & kelola film/review
│   ├── tambah_film.php        # Form tambah film baru
│   ├── edit_film.php          # Form edit film
│   ├── hapus_film.php         # Proses hapus film
│   ├── hapus_review.php       # Proses hapus review
│   └── kelola_review.php      # Halaman kelola review
├── user/                     # Halaman khusus admin
│   ├── dashboard.php          # Statistik & kelola film/review
│   ├── edit_review.php        # Form edit review
│   └── hapus_review.php       # Proses hapus review
├── img/                       # Poster film
│   └── default.jpg
│
└── database/
    └── movie_review.sql       # Struktur & data awal database
```

## 🌐 Alamat Website

```
http://localhost/matchaflix/
```

---

*© 2026 MatchaFlix · Sip manis seperti matcha latte 🍃*
