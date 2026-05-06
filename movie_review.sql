USE movie_review;

-- Tabel daftar film
CREATE TABLE film (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    poster VARCHAR(255) DEFAULT 'default.jpg',
    deskripsi TEXT
);

-- Tabel review (terhubung ke film)
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    film_id INT NOT NULL,
    nama_reviewer VARCHAR(100),
    review TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (film_id) REFERENCES film(id) ON DELETE CASCADE
);

-- Contoh data film awal
INSERT INTO film (judul, poster, deskripsi) VALUES
('Your Name.', 'yourname.jpg', 'Film anime tentang dua orang yang bertukar tubuh.'),
('Spider-Man: No Way Home', 'spiderman.jpg', 'Petualangan Spider-Man di multiverse.'),
('Kingdom', 'kingdom.jpg', 'Series zombie di era kerajaan Korea.'),
('Alice in Borderland', 'alice.jpg', 'Series survival thriller di Tokyo alternatif.');