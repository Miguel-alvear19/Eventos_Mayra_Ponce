-- =====================================================
-- BASE DE DATOS: Eventos Mayra Ponce
-- Ejecutar completo en phpMyAdmin (pestaña SQL) o en la consola de MySQL.
-- Usa "IF NOT EXISTS": si una tabla ya existe (por ejemplo "clientes")
-- no se borra ni se modifica, así no pierdes datos.
-- =====================================================

CREATE DATABASE IF NOT EXISTS Eventos_Mayra_Ponce
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE Eventos_Mayra_Ponce;

-- -----------------------------------------------------
-- CLIENTES (ya la tenías: se deja igual si existe)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente      INT AUTO_INCREMENT PRIMARY KEY,
    nombres         VARCHAR(80)  NOT NULL,
    apellidos       VARCHAR(80)  NOT NULL DEFAULT '',
    correo          VARCHAR(120) NOT NULL UNIQUE,
    telefono        VARCHAR(20)  NULL,
    password        VARCHAR(255) NOT NULL,
    estado          ENUM('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
    fecha_registro  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- ADMINISTRADORES (entran por el mismo login)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS administradores (
    id_admin    INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80)  NOT NULL,
    correo      VARCHAR(120) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- RESERVAS (se crean desde "Solicitar reserva")
-- estado: Pendiente -> Aprobada / Rechazada, o Cancelada por el cliente
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS reservas (
    id_reserva       INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente       INT NOT NULL,
    nombre_contacto  VARCHAR(120) NOT NULL,
    correo_contacto  VARCHAR(120) NOT NULL,
    fecha_evento     DATE NOT NULL,
    hora_evento      TIME NOT NULL,
    tipo_evento      VARCHAR(40)  NOT NULL,
    invitados        INT NOT NULL,
    servicios        VARCHAR(255) NULL,
    notas            TEXT NULL,
    estado           ENUM('Pendiente','Aprobada','Rechazada','Cancelada') NOT NULL DEFAULT 'Pendiente',
    fecha_solicitud  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha_evento),
    CONSTRAINT fk_reserva_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- COTIZACIONES (se crean desde "Solicitar cotización")
-- id_cliente puede ser NULL si la pide alguien sin cuenta
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS cotizaciones (
    id_cotizacion    INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente       INT NULL,
    nombre_contacto  VARCHAR(120) NOT NULL,
    correo_contacto  VARCHAR(120) NOT NULL,
    telefono         VARCHAR(20)  NULL,
    tipo_evento      VARCHAR(40)  NOT NULL,
    fecha_tentativa  DATE NULL,
    invitados        INT NOT NULL,
    servicios        VARCHAR(255) NULL,
    presupuesto      VARCHAR(60)  NULL,
    mensaje          TEXT NULL,
    estado           ENUM('Pendiente','Respondida') NOT NULL DEFAULT 'Pendiente',
    monto_estimado   DECIMAL(12,2) NULL,
    respuesta        TEXT NULL,
    fecha_solicitud  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cotizacion_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- FECHAS BLOQUEADAS (días de mantenimiento del salón)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS fechas_bloqueadas (
    fecha   DATE PRIMARY KEY,
    motivo  VARCHAR(120) NOT NULL DEFAULT 'Mantenimiento'
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- ADMINISTRADOR POR DEFECTO
-- correo:     admin@eventosmayraponce.com
-- contraseña: Admin2026*      (cámbiala desde Configuración)
-- -----------------------------------------------------
INSERT IGNORE INTO administradores (nombre, correo, password) VALUES
('Mayra Ponce', 'admin@eventosmayraponce.com',
 '$2y$10$9wOz6hLpmpUtmNwcAKt3O.ZgxogClKKPZ0ImMxeJGB46.E0aVVknO');
