CREATE DATABASE IF NOT EXISTS gamestudio_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE gamestudio_db;

DROP TABLE IF EXISTS estudios;

CREATE TABLE estudios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    pais VARCHAR(100) NULL,
    ciudad VARCHAR(100) NULL,
    fecha_fundacion DATE NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO estudios (nombre, pais, ciudad, fecha_fundacion, activo) VALUES
('Nintendo EPD', 'Japón', 'Kioto', '1983-09-30', TRUE),
('Rockstar North', 'Reino Unido', 'Edimburgo', '1987-01-01', TRUE),
('Ubisoft Montreal', 'Canadá', 'Montreal', '1997-04-25', TRUE),
('CD Projekt Red', 'Polonia', 'Varsovia', '2002-02-01', TRUE),
('MercurySteam', 'España', 'Madrid', '2002-01-01', TRUE),
('Pyro Studios', 'España', 'Madrid', '1996-01-01', FALSE);