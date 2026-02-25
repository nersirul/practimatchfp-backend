/* * NOMBRE DEL SCRIPT: ddl_practimatch.sql
 * PROYECTO: PractiMatchFP
 * DESCRIPCION: Script DDL para la creación de tablas y relaciones.
 * AUTOR: Guillermo Ogallar Miranda
 */

DROP DATABASE IF EXISTS practimatch_db;
CREATE DATABASE practimatch_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practimatch_db;

-- 1. Tabla ADMINISTRADOR
CREATE TABLE administrador (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Se almacenará hash
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabla EMPRESA
CREATE TABLE empresa (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY,
    nombre_comercial VARCHAR(150) NOT NULL,
    cif VARCHAR(20) NOT NULL UNIQUE,
    direccion VARCHAR(200),
    ciudad VARCHAR(100),
    num_trabajadores INT,
    sector VARCHAR(100),
    email_contacto VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabla ALUMNO
CREATE TABLE alumno (
    id_alumno INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    nif VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ciclo_formativo VARCHAR(100),
    modalidad_preferida ENUM('REMOTO', 'PRESENCIAL', 'HIBRIDO') DEFAULT 'PRESENCIAL',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Tabla CATEGORIA
CREATE TABLE categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 5. Tabla TECNOLOGIA
CREATE TABLE tecnologia (
    id_tecnologia INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 6. Tabla Pivote TECNOLOGIA_CATEGORIA (N:M)
CREATE TABLE tecnologia_categoria (
    id_tecnologia INT,
    id_categoria INT,
    PRIMARY KEY (id_tecnologia, id_categoria),
    FOREIGN KEY (id_tecnologia) REFERENCES tecnologia(id_tecnologia) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Tabla OFERTA
CREATE TABLE oferta (
    id_oferta INT AUTO_INCREMENT PRIMARY KEY,
    id_empresa INT NOT NULL,
    id_admin_validador INT NULL, -- Puede ser NULL si aún no se valida
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    modalidad ENUM('REMOTO', 'PRESENCIAL', 'HIBRIDO') NOT NULL,
    es_remunerada BOOLEAN DEFAULT FALSE,
    posibilidad_contratacion BOOLEAN DEFAULT FALSE,
    estado ENUM('PENDIENTE', 'PUBLICADA', 'CERRADA') DEFAULT 'PENDIENTE',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE,
    FOREIGN KEY (id_admin_validador) REFERENCES administrador(id_admin) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 8. Tabla Pivote OFERTA_TECNOLOGIA (N:M)
CREATE TABLE oferta_tecnologia (
    id_oferta INT,
    id_tecnologia INT,
    PRIMARY KEY (id_oferta, id_tecnologia),
    FOREIGN KEY (id_oferta) REFERENCES oferta(id_oferta) ON DELETE CASCADE,
    FOREIGN KEY (id_tecnologia) REFERENCES tecnologia(id_tecnologia) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Tabla Pivote ALUMNO_TECNOLOGIA (N:M con discriminador)
CREATE TABLE alumno_tecnologia (
    id_alumno INT,
    id_tecnologia INT,
    tipo_relacion ENUM('CONOCE', 'INTERES') NOT NULL,
    nivel INT DEFAULT 0, -- 0 a 10, solo relevante si es CONOCE
    PRIMARY KEY (id_alumno, id_tecnologia, tipo_relacion),
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE,
    FOREIGN KEY (id_tecnologia) REFERENCES tecnologia(id_tecnologia) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Tabla PRACTICA (Match)
CREATE TABLE practica (
    id_practica INT AUTO_INCREMENT PRIMARY KEY,
    id_oferta INT NOT NULL,
    id_alumno INT NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('SOLICITADA', 'EN_CURSO', 'FINALIZADA', 'CANCELADA') DEFAULT 'SOLICITADA',
    FOREIGN KEY (id_oferta) REFERENCES oferta(id_oferta) ON DELETE CASCADE,
    FOREIGN KEY (id_alumno) REFERENCES alumno(id_alumno) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Tabla SEGUIMIENTO
CREATE TABLE seguimiento (
    id_seguimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_practica INT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('ENCUESTA_ALUMNO', 'ENCUESTA_TUTOR') DEFAULT 'ENCUESTA_ALUMNO',
    respuestas_json JSON NULL, -- Guardamos las respuestas flexiblemente
    completado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_practica) REFERENCES practica(id_practica) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 12. Tabla VALORACION
CREATE TABLE valoracion (
    id_valoracion INT AUTO_INCREMENT PRIMARY KEY,
    id_practica INT NOT NULL UNIQUE, -- Una valoración por práctica
    puntuacion TINYINT UNSIGNED CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_practica) REFERENCES practica(id_practica) ON DELETE CASCADE
) ENGINE=InnoDB;


-- INSERCIONES
-- 1. Insertar ADMINISTRADORES
INSERT INTO administrador (nombre, email, password) VALUES 
('Super Admin', 'admin@practimatch.com', 'sha256hashedpassword'),
('Gestor Prácticas', 'gestor@practimatch.com', 'sha256hashedpassword');

-- 2. Insertar CATEGORIAS
INSERT INTO categoria (nombre) VALUES 
('Desarrollo Web'), 
('Sistemas y Redes'), 
('Ciberseguridad'), 
('Data Science'), 
('Desarrollo Móvil'), 
('Scripting');

-- 3. Insertar TECNOLOGIAS
INSERT INTO tecnologia (nombre) VALUES 
('PHP'), ('JavaScript'), ('Python'), ('Java'), ('Linux'), ('Docker'), ('React'), ('MySQL'), ('AWS'), ('Swift');

-- 4. Relacionar TECNOLOGIAS con CATEGORIAS (Aquí probamos la relación N:M)
-- Python es Scripting, Web y Data Science
INSERT INTO tecnologia_categoria (id_tecnologia, id_categoria) VALUES 
(3, 6), (3, 1), (3, 4), 
-- PHP es Web
(1, 1),
-- JavaScript es Web
(2, 1),
-- Linux es Sistemas y Ciberseguridad
(5, 2), (5, 3);

-- 5. Insertar EMPRESAS
INSERT INTO empresa (nombre_comercial, cif, email_contacto, password, ciudad, sector) VALUES 
('TechSolutions SL', 'B12345678', 'rrhh@techsolutions.com', 'pass123', 'Madrid', 'Consultoría'),
('DevHouse Studio', 'B87654321', 'jobs@devhouse.com', 'pass123', 'Barcelona', 'Software Factory'),
('CyberGuard', 'A11223344', 'info@cyberguard.es', 'pass123', 'Valencia', 'Seguridad'),
('DataMinds', 'B99887766', 'contact@dataminds.com', 'pass123', 'Remoto', 'Big Data'),
('AppFactory', 'B55443322', 'rrhh@appfactory.com', 'pass123', 'Sevilla', 'Movilidad');

-- 6. Insertar ALUMNOS
INSERT INTO alumno (nombre, apellidos, nif, email, password, ciclo, modalidad_preferida) VALUES 
('Juan', 'Pérez', '12345678A', 'juan@fp.com', 'pass', 'DAW', 'PRESENCIAL'),
('Ana', 'Gómez', '87654321B', 'ana@fp.com', 'pass', 'DAM', 'REMOTO'),
('Luis', 'Martínez', '11223344C', 'luis@fp.com', 'pass', 'ASIR', 'HIBRIDO'),
('Maria', 'López', '55667788D', 'maria@fp.com', 'pass', 'DAW', 'HIBRIDO'),
('Carlos', 'Ruiz', '99887766E', 'carlos@fp.com', 'pass', 'ASIR', 'PRESENCIAL');

-- 7. Insertar Habilidades e Intereses de Alumnos
-- Juan sabe PHP y MySQL, quiere aprender Python
INSERT INTO alumno_tecnologia (id_alumno, id_tecnologia, tipo_relacion, nivel) VALUES 
(1, 1, 'CONOCE', 8), (1, 8, 'CONOCE', 7), (1, 3, 'INTERES', 0),
-- Ana sabe Java y Swift, quiere aprender React
(2, 4, 'CONOCE', 9), (2, 10, 'CONOCE', 6), (2, 7, 'INTERES', 0);

-- 8. Insertar OFERTAS
INSERT INTO oferta (id_empresa, id_admin_validador, titulo, descripcion, modalidad, estado, fecha_creacion) VALUES 
-- Oferta Publicada y Validada (TechSolutions)
(1, 1, 'Junior Web Developer', 'Desarrollo backend con PHP', 'PRESENCIAL', 'PUBLICADA', '2023-09-01 10:00:00'),
-- Oferta Pendiente (DevHouse)
(2, NULL, 'Frontend React Developer', 'Maquetación y lógica con React', 'REMOTO', 'PENDIENTE', '2023-10-01 09:00:00'),
-- Oferta Cerrada (CyberGuard)
(3, 1, 'Becario SysAdmin', 'Administración Linux', 'PRESENCIAL', 'CERRADA', '2023-08-01 10:00:00'),
-- Oferta Publicada (DataMinds)
(4, 2, 'Data Analyst Junior', 'Python y Pandas', 'REMOTO', 'PUBLICADA', '2023-09-15 11:30:00'),
-- Otra oferta TechSolutions
(1, 1, 'Fullstack Dev', 'PHP y JS', 'HIBRIDO', 'PUBLICADA', '2023-09-20 12:00:00');

-- 9. Requisitos de las OFERTAS
-- Junior Web Developer requiere PHP y MySQL
INSERT INTO oferta_tecnologia (id_oferta, id_tecnologia) VALUES (1, 1), (1, 8);
-- Data Analyst requiere Python
INSERT INTO oferta_tecnologia (id_oferta, id_tecnologia) VALUES (4, 3);
-- SysAdmin requiere Linux
INSERT INTO oferta_tecnologia (id_oferta, id_tecnologia) VALUES (3, 5);

-- 10. Insertar PRACTICAS (Matches)
-- Juan en TechSolutions (Finalizada)
INSERT INTO practica (id_oferta, id_alumno, fecha_inicio, fecha_fin, estado) VALUES 
(1, 1, '2023-09-10', '2023-12-10', 'FINALIZADA'),
-- Ana en DataMinds (En curso)
(4, 2, '2023-10-01', NULL, 'EN_CURSO');

-- 11. Insertar SEGUIMIENTOS
INSERT INTO seguimiento (id_practica, tipo, completado, datos_encuesta) VALUES 
(1, 'ENCUESTA_ALUMNO', TRUE, '{"satisfaccion": 5, "aprendizaje": "Alto"}'),
(2, 'ENCUESTA_ALUMNO', FALSE, NULL);

-- 12. Insertar VALORACION FINAL
-- Juan valora a TechSolutions
INSERT INTO valoracion (id_practica, puntuacion, comentario, fecha_registro) VALUES 
(1, 5, 'Excelente ambiente y mucho aprendizaje en PHP.', '2023-12-11 10:00:00');