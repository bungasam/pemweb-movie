-- =============================================
--  DATABASE: movie_review
--  Proyek: CineView - Website Rating Film
--  Buat database ini dulu di phpMyAdmin
-- =============================================


USE movie_review1;

-- ==============================
-- TABEL: users (menyimpan data pengguna)
-- ==============================
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,  -- ID unik, otomatis naik
    username    VARCHAR(50) NOT NULL UNIQUE,     -- Nama pengguna, tidak boleh sama
    email       VARCHAR(100) NOT NULL UNIQUE,    -- Email, tidak boleh sama
    password    VARCHAR(255) NOT NULL,           -- Password (sudah di-hash)
    role        ENUM('admin','user') DEFAULT 'user', -- Peran: admin atau user biasa
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP  -- Waktu mendaftar
);

-- ==============================
-- TABEL: films (menyimpan data film)
-- ==============================
CREATE TABLE films (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    judul       VARCHAR(200) NOT NULL,           -- Judul film
    genre       VARCHAR(100),                   -- Genre: Action, Drama, dll
    tahun       YEAR,                           -- Tahun rilis
    sutradara   VARCHAR(100),                   -- Nama sutradara
    sinopsis    TEXT,                           -- Deskripsi panjang film
    poster      VARCHAR(255) DEFAULT 'default.jpg', -- Nama file gambar poster
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==============================
-- TABEL: reviews (menyimpan review & rating)
-- ==============================
CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,                   -- Siapa yang review
    film_id     INT NOT NULL,                   -- Film apa yang di-review
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5), -- Bintang 1-5
    komentar    TEXT,                           -- Isi komentar/ulasan
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- Satu user hanya bisa review 1 film sekali
    UNIQUE KEY unik_review (user_id, film_id),
    -- Hubungkan ke tabel users dan films
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE
);

-- ==============================
-- DATA AWAL: Akun Admin Default
-- Password: admin123 (sudah di-hash dengan password_hash)
-- ==============================
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@cineview.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ==============================
-- DATA AWAL: Beberapa Film Contoh
-- ==============================
INSERT INTO films (judul, genre, tahun, sutradara, sinopsis) VALUES
('Parasite', 'Drama, Thriller', 2019, 'Bong Joon-ho',
 'Sebuah keluarga miskin perlahan menyusup ke kehidupan keluarga kaya dan terjadi berbagai peristiwa yang tidak terduga.'),

('Interstellar', 'Sci-Fi, Drama', 2014, 'Christopher Nolan',
 'Sekelompok penjelajah melakukan perjalanan melalui lubang cacing di luar angkasa dalam upaya memastikan kelangsungan hidup manusia.'),

('Dune', 'Sci-Fi, Adventure', 2021, 'Denis Villeneuve',
 'Paul Atreides, seorang pemuda berbakat dari keluarga bangsawan, datang ke planet paling berbahaya di alam semesta untuk mengamankan masa depan keluarganya.'),

('The Batman', 'Action, Crime', 2022, 'Matt Reeves',
 'Di tahun keduanya melawan kejahatan, Batman harus mengungkap korupsi yang merajalela di Gotham saat Riddler menargetkan para tokoh elite kota.'),

('Everything Everywhere All at Once', 'Comedy, Sci-Fi', 2022, 'The Daniels',
 'Seorang ibu imigran China-Amerika yang kewalahan menemukan dirinya dapat mengakses keterampilan dari versi-versi dirinya di multiverse lain.');

-- Cek data berhasil masuk
SELECT 'Database berhasil dibuat!' AS status;