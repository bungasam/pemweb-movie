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
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    foto        VARCHAR(255) DEFAULT NULL,        -- Foto profil (opsional)
    role        ENUM('admin','user') DEFAULT 'user',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==============================
-- TABEL: films (menyimpan data film)
-- ==============================
CREATE TABLE films (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    judul       VARCHAR(200) NOT NULL,
    genre       VARCHAR(200),                    -- Bisa multi genre, contoh: "Action, Drama"
    tahun       YEAR,
    sutradara   VARCHAR(100),
    sinopsis    TEXT,
    poster      VARCHAR(255) DEFAULT 'default.jpg',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==============================
-- TABEL: reviews (menyimpan review & rating)
-- ==============================
CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    film_id     INT NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar    TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unik_review (user_id, film_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE
);

-- ==============================
-- DATA AWAL: Akun Admin Default
-- Password: admin123
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

SELECT 'Database berhasil dibuat!' AS status;
