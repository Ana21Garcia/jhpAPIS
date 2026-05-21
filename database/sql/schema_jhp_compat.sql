-- Compatibilidad para usar el dump jhp con las funciones ya integradas en JHP/jhpAPI.
-- Ejecutar sobre la base local `jhp` si se importo primero el dump base.

CREATE DATABASE IF NOT EXISTS jhp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jhp;

CREATE TABLE IF NOT EXISTS cache (
  `key` varchar(255) NOT NULL PRIMARY KEY,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  KEY cache_expiration_index (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cache_locks (
  `key` varchar(255) NOT NULL PRIMARY KEY,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  KEY cache_locks_expiration_index (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS failed_jobs (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uuid` varchar(255) NOT NULL UNIQUE,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  KEY jobs_queue_index (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS job_batches (
  `id` varchar(255) NOT NULL PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
  `id` varchar(255) NOT NULL PRIMARY KEY,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  KEY sessions_user_id_index (`user_id`),
  KEY sessions_last_activity_index (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  `email` varchar(255) NOT NULL PRIMARY KEY,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS personal_access_tokens (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL UNIQUE,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY personal_access_tokens_tokenable_type_tokenable_id_index (`tokenable_type`, `tokenable_id`),
  KEY personal_access_tokens_expires_at_index (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE clientes
  ADD COLUMN IF NOT EXISTS cli_telefonos_extra JSON NULL AFTER cli_telefono,
  ADD COLUMN IF NOT EXISTS tipo_usuario TINYINT UNSIGNED NOT NULL DEFAULT 3 AFTER cli_correo,
  ADD COLUMN IF NOT EXISTS cli_estado ENUM('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE empleados
  ADD COLUMN IF NOT EXISTS tipo_usuario TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER emp_rol,
  ADD COLUMN IF NOT EXISTS es_mecanico TINYINT(1) NOT NULL DEFAULT 0 AFTER tipo_usuario,
  ADD COLUMN IF NOT EXISTS emp_usuario VARCHAR(100) NULL AFTER es_mecanico,
  ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT NULL;

UPDATE empleados
SET emp_usuario = COALESCE(NULLIF(emp_usuario, ''), emp_correo, CONCAT('empleado', id_empleados))
WHERE emp_usuario IS NULL OR emp_usuario = '';

ALTER TABLE proveedores
  ADD COLUMN IF NOT EXISTS productos_sucursal JSON NULL AFTER prov_direccion,
  ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE cotizaciones
  ADD COLUMN IF NOT EXISTS cot_estado VARCHAR(20) NOT NULL DEFAULT 'Vigente' AFTER cot_vigencia_dias;

CREATE TABLE IF NOT EXISTS productos (
  id_producto INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pro_codigo VARCHAR(50) NOT NULL UNIQUE,
  pro_nombre VARCHAR(100) NOT NULL,
  pro_tipo VARCHAR(50) DEFAULT NULL,
  pro_marca VARCHAR(50) DEFAULT NULL,
  pro_descripcion TEXT DEFAULT NULL,
  pro_precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0,
  pro_iva DECIMAL(10,2) NOT NULL DEFAULT 0,
  pro_stock INT NOT NULL DEFAULT 0,
  pro_categoria VARCHAR(80) DEFAULT NULL,
  pro_proveedor VARCHAR(120) DEFAULT NULL,
  id_categoria INT UNSIGNED DEFAULT NULL,
  id_proveedor INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  KEY productos_pro_codigo_index (pro_codigo),
  KEY productos_pro_stock_index (pro_stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO productos (pro_codigo, pro_nombre, pro_tipo, pro_marca, pro_descripcion, pro_precio_venta, pro_stock, id_categoria, id_proveedor)
SELECT p.pro_codigo, p.pro_nombre, p.pro_tipo, p.pro_marca, p.pro_descripcion, p.pro_precio_venta, p.pro_stock, p.id_categoria, p.id_proveedor
FROM producto p
LEFT JOIN productos pp ON pp.pro_codigo = p.pro_codigo
WHERE pp.id_producto IS NULL;

CREATE TABLE IF NOT EXISTS inventarios (
  id_inventario INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_producto INT UNSIGNED DEFAULT NULL,
  codigo_producto VARCHAR(50) NOT NULL,
  nombre_producto VARCHAR(120) NOT NULL,
  marca VARCHAR(80) DEFAULT NULL,
  categoria VARCHAR(80) DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
  iva DECIMAL(10,2) NOT NULL DEFAULT 0,
  precio_total DECIMAL(10,2) NOT NULL DEFAULT 0,
  id_proveedor INT UNSIGNED DEFAULT NULL,
  proveedor VARCHAR(120) DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY inventarios_producto_unico (codigo_producto, marca, categoria),
  KEY inventarios_stock_index (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO inventarios (id_producto, codigo_producto, nombre_producto, marca, categoria, stock, precio_unitario, iva, precio_total, id_proveedor, proveedor, created_at, updated_at)
SELECT p.id_producto, p.pro_codigo, p.pro_nombre, p.pro_marca, p.pro_categoria, p.pro_stock, p.pro_precio_venta, p.pro_iva, p.pro_precio_venta + p.pro_iva, p.id_proveedor, p.pro_proveedor, NOW(), NOW()
FROM productos p
LEFT JOIN inventarios i ON i.codigo_producto = p.pro_codigo
WHERE i.id_inventario IS NULL;
