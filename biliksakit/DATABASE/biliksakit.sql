-- Database: biliksakit
-- Sistem Pengurusan Kesihatan Pelajar - Bilik Sakit Asrama KVSP1

CREATE DATABASE IF NOT EXISTS biliksakit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biliksakit;

-- Table: users (Biro dan Petugas)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    peranan ENUM('biro', 'petugas') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: pelajar (Data pelajar)
CREATE TABLE IF NOT EXISTS pelajar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_matrik VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(50),
    program VARCHAR(100),
    kursus VARCHAR(100),
    no_telefon VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: bilik (Bilik isolasi)
CREATE TABLE IF NOT EXISTS bilik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_bilik VARCHAR(10) NOT NULL UNIQUE,
    nama_bilik VARCHAR(50),
    kapasiti INT DEFAULT 1,
    status ENUM('tersedia', 'digunakan', 'pembersihan', 'rosak') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: permohonan (Permohonan masuk bilik sakit)
CREATE TABLE IF NOT EXISTS permohonan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelajar_id INT NOT NULL,
    biro_id INT NOT NULL,
    petugas_id INT NULL,
    bilik_id INT NULL,
    tarikh_sakit DATE NOT NULL,
    masa_sakit TIME NOT NULL,
    simptom TEXT NOT NULL,
    catatan TEXT,
    
    -- Column untuk dokumen dan gambar (DITAMBAH)
    dokumen_sokongan VARCHAR(255) NULL,
    gambar_camera VARCHAR(255) NULL,
    
    status ENUM('menunggu', 'diluluskan', 'ditolak') DEFAULT 'menunggu',
    tarikh_diluluskan DATETIME NULL,
    tarikh_ditolak DATETIME NULL,
    catatan_petugas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (pelajar_id) REFERENCES pelajar(id) ON DELETE CASCADE,
    FOREIGN KEY (biro_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (bilik_id) REFERENCES bilik(id) ON DELETE SET NULL,
    
    -- Index untuk performance
    INDEX idx_status (status),
    INDEX idx_pelajar_id (pelajar_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: aktiviti_pelajar (Log aktiviti pelajar di bilik sakit)
CREATE TABLE IF NOT EXISTS aktiviti_pelajar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permohonan_id INT NOT NULL,
    tarikh DATE NOT NULL,
    masa TIME NOT NULL,
    suhu_badan DECIMAL(3,1),
    tekanan_darah VARCHAR(20),
    catatan_kesihatan TEXT,
    ubat_diberi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (permohonan_id) REFERENCES permohonan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default users
-- Password: admin123 (hashed using bcrypt)
INSERT INTO users (username, password, nama, peranan) VALUES
('biro1', '$2y$10$KYYTMhcyq6vzs8r/Kt8B5Ong27TzgEIfE1eDT5ATBSX.QqzSAWh0i', 'Biro Kesihatan 1', 'biro'),
('biro2', '$2y$10$KYYTMhcyq6vzs8r/Kt8B5Ong27TzgEIfE1eDT5ATBSX.QqzSAWh0i', 'Biro Kesihatan 2', 'biro'),
('petugas1', '$2y$10$KYYTMhcyq6vzs8r/Kt8B5Ong27TzgEIfE1eDT5ATBSX.QqzSAWh0i', 'Petugas Kesihatan 1', 'petugas'),
('petugas2', '$2y$10$KYYTMhcyq6vzs8r/Kt8B5Ong27TzgEIfE1eDT5ATBSX.QqzSAWh0i', 'Petugas Kesihatan 2', 'petugas');

-- Insert sample bilik
INSERT INTO bilik (no_bilik, nama_bilik, kapasiti, status) VALUES
('B001', 'Bilik Isolasi 1', 2, 'tersedia'),
('B002', 'Bilik Isolasi 2', 2, 'tersedia'),
('B003', 'Bilik Isolasi 3', 1, 'tersedia'),
('B004', 'Bilik Isolasi 4', 1, 'tersedia'),
('B005', 'Bilik Isolasi 5', 2, 'tersedia'),
('B006', 'Bilik Isolasi 6', 1, 'tersedia'),
('B007', 'Bilik Isolasi 7', 2, 'digunakan'),
('B008', 'Bilik Isolasi 8', 1, 'pembersihan');

-- Insert sample pelajar
INSERT INTO pelajar (no_matrik, nama, kelas, program, kursus, no_telefon, alamat) VALUES
('KVSP2024001', 'Ahmad bin Abdullah', '4A', 'Teknologi Maklumat', 'Pengaturcaraan Web', '0123456789', 'Asrama Blok A, Bilik 101'),
('KVSP2024002', 'Siti Nurhaliza', '4B', 'Seni Kreatif', 'Rekabentuk Grafik', '0123456790', 'Asrama Blok B, Bilik 205'),
('KVSP2024003', 'Muhammad Ali', '4C', 'Kejuruteraan', 'Elektrik', '0123456791', 'Asrama Blok C, Bilik 312'),
('KVSP2024004', 'Nurul Huda', '4D', 'Perakaunan', 'Kewangan', '0123456792', 'Asrama Blok D, Bilik 408'),
('KVSP2024005', 'Lim Wei Jie', '4E', 'Pemasaran', 'Pengurusan Perniagaan', '0123456793', 'Asrama Blok E, Bilik 502');

-- Insert sample permohonan (untuk testing)
INSERT INTO permohonan (pelajar_id, biro_id, tarikh_sakit, masa_sakit, simptom, catatan, status) VALUES
(1, 1, CURDATE(), '08:30:00', 'Demam, batuk, selsema', 'Suhu badan 38.5°C', 'menunggu'),
(2, 1, CURDATE(), '10:15:00', 'Sakit kepala, pening', 'Tidak hadir kelas pagi', 'diluluskan'),
(3, 2, CURDATE(), '14:20:00', 'Sakit perut, cirit-birit', 'Makan makanan pedas', 'ditolak');

-- Update one permohonan with bilik (for testing)
UPDATE permohonan SET 
    petugas_id = 3,
    bilik_id = 1,
    tarikh_diluluskan = NOW(),
    catatan_petugas = 'Pelajar memerlukan rehat dan pemantauan'
WHERE id = 2;

-- Create view for easy reporting
CREATE OR REPLACE VIEW vw_permohonan_detail AS
SELECT 
    p.id,
    p.tarikh_sakit,
    p.masa_sakit,
    p.simptom,
    p.catatan,
    p.dokumen_sokongan,
    p.gambar_camera,
    p.status,
    p.tarikh_diluluskan,
    p.tarikh_ditolak,
    p.catatan_petugas,
    p.created_at,
    -- Pelajar info
    pl.no_matrik,
    pl.nama as nama_pelajar,
    pl.kelas,
    pl.program,
    pl.kursus,
    pl.no_telefon,
    -- Biro info
    ub.nama as nama_biro,
    ub.peranan as peranan_biro,
    -- Petugas info
    up.nama as nama_petugas,
    up.peranan as peranan_petugas,
    -- Bilik info
    b.no_bilik,
    b.nama_bilik,
    b.status as status_bilik
FROM permohonan p
LEFT JOIN pelajar pl ON p.pelajar_id = pl.id
LEFT JOIN users ub ON p.biro_id = ub.id
LEFT JOIN users up ON p.petugas_id = up.id
LEFT JOIN bilik b ON p.bilik_id = b.id;

-- Create view for dashboard statistics
CREATE OR REPLACE VIEW vw_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM permohonan WHERE status = 'menunggu') as menunggu,
    (SELECT COUNT(*) FROM permohonan WHERE status = 'diluluskan') as diluluskan,
    (SELECT COUNT(*) FROM permohonan WHERE status = 'ditolak') as ditolak,
    (SELECT COUNT(*) FROM bilik WHERE status = 'tersedia') as bilik_tersedia,
    (SELECT COUNT(*) FROM bilik WHERE status = 'digunakan') as bilik_digunakan,
    (SELECT COUNT(*) FROM pelajar) as total_pelajar;

-- Stored procedure for monthly report
DELIMITER //
CREATE PROCEDURE sp_monthly_report(IN month INT, IN year INT)
BEGIN
    SELECT 
        DATE(p.created_at) as tarikh,
        COUNT(*) as jumlah_permohonan,
        SUM(CASE WHEN p.status = 'diluluskan' THEN 1 ELSE 0 END) as diluluskan,
        SUM(CASE WHEN p.status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
        GROUP_CONCAT(DISTINCT pl.nama SEPARATOR ', ') as pelajar
    FROM permohonan p
    LEFT JOIN pelajar pl ON p.pelajar_id = pl.id
    WHERE MONTH(p.created_at) = month AND YEAR(p.created_at) = year
    GROUP BY DATE(p.created_at)
    ORDER BY tarikh;
END //
DELIMITER ;

-- Insert sample data for aktiviti pelajar
INSERT INTO aktiviti_pelajar (permohonan_id, tarikh, masa, suhu_badan, tekanan_darah, catatan_kesihatan, ubat_diberi) VALUES
(2, CURDATE(), '08:00:00', 37.5, '120/80', 'Kondisi stabil, rehat cukup', 'Paracetamol 500mg'),
(2, CURDATE(), '14:00:00', 37.2, '118/78', 'Demam sudah turun, selera makan bertambah', 'Vitamin C');

-- Create trigger to update bilik status when permohonan is approved
DELIMITER //
CREATE TRIGGER trg_permohonan_approved
AFTER UPDATE ON permohonan
FOR EACH ROW
BEGIN
    IF NEW.status = 'diluluskan' AND OLD.status != 'diluluskan' THEN
        -- Update bilik status to digunakan
        UPDATE bilik SET status = 'digunakan' WHERE id = NEW.bilik_id;
    END IF;
    
    IF NEW.status = 'ditolak' AND OLD.status = 'diluluskan' AND OLD.bilik_id IS NOT NULL THEN
        -- Update bilik status back to tersedia
        UPDATE bilik SET status = 'tersedia' WHERE id = OLD.bilik_id;
    END IF;
END //
DELIMITER ;

-- Create trigger for automatic timestamp update
DELIMITER //
CREATE TRIGGER trg_permohonan_before_update
BEFORE UPDATE ON permohonan
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
    
    -- Set tarikh_diluluskan or tarikh_ditolak automatically
    IF NEW.status = 'diluluskan' AND OLD.status != 'diluluskan' THEN
        SET NEW.tarikh_diluluskan = CURRENT_TIMESTAMP;
        SET NEW.tarikh_ditolak = NULL;
    END IF;
    
    IF NEW.status = 'ditolak' AND OLD.status != 'ditolak' THEN
        SET NEW.tarikh_ditolak = CURRENT_TIMESTAMP;
        SET NEW.tarikh_diluluskan = NULL;
    END IF;
END //
DELIMITER ;

-- Grant privileges (example - adjust according to your setup)
-- CREATE USER IF NOT EXISTS 'biliksakit_user'@'localhost' IDENTIFIED BY 'secure_password123';
-- GRANT ALL PRIVILEGES ON biliksakit.* TO 'biliksakit_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Show success message
SELECT 'Database biliksakit berjaya dicipta!' as Message;