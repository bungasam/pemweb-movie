USE movie_review;

-- Tabel users (admin & user biasa)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel daftar film
CREATE TABLE film (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    poster VARCHAR(255) DEFAULT 'default.jpg',
    deskripsi TEXT,
    genre VARCHAR(100),
    tahun INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel review (terhubung ke film dan user)
CREATE TABLE review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    film_id INT NOT NULL,
    user_id INT NOT NULL,
    nama_reviewer VARCHAR(100),
    review TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (film_id) REFERENCES film(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert data awal
INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin'),
('user1', MD5('user123'), 'user');

INSERT INTO film (judul, poster, deskripsi, genre, tahun) VALUES
('Your Name.', 'yourname.jpg', 'Film anime tentang dua orang yang bertukar tubuh dan berusaha menemukan satu sama lain melintasi waktu dan ruang.', 'Animation', 2016),
('Spider-Man: No Way Home', 'spiderman.jpg', 'Petualangan Spider-Man di multiverse dengan kembalinya villain-villain klasik.', 'Action', 2021),
('Kingdom', 'kingdom.jpg', 'Series zombie di era kerajaan Korea dengan intrik politik yang menegangkan.', 'Thriller', 2019),
('Alice in Borderland', 'alice.jpg', 'Series survival thriller di Tokyo alternatif dimana karakter harus bermain game mematikan.', 'Mystery', 2020),
('Oppenheimer', 'oppenheimer.jpg', 'Kisah J. Robert Oppenheimer dan penciptaan bom atom.', 'Drama', 2023),
('Suzume', 'suzume.jpg', 'Petualangan seorang gadis yang berusaha menutup pintu-pintu bencana di seluruh Jepang.', 'Animation', 2022);