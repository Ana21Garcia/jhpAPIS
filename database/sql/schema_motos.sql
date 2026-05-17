-- Script SQL para crear todas las tablas en la base de datos 'motos'
-- Este script contiene todas las tablas del sistema JHP API

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS motos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE motos;

-- Tabla de Categorías
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    cat_nombre VARCHAR(50) NOT NULL,
    cat_descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (cat_nombre)
);

-- Tabla de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    cli_nombre VARCHAR(100) NOT NULL,
    cli_apaterno VARCHAR(50),
    cli_amaterno VARCHAR(50),
    cli_telefono VARCHAR(15),
    cli_correo VARCHAR(100),
    cli_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_correo (cli_correo),
    INDEX idx_telefono (cli_telefono)
);

-- Tabla de Proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    prov_nombre VARCHAR(100) NOT NULL,
    prov_contacto VARCHAR(100),
    prov_telefono VARCHAR(15),
    prov_email VARCHAR(100),
    prov_direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (prov_email)
);

-- Tabla de Empleados (tabla original, se mantiene para compatibilidad)
CREATE TABLE IF NOT EXISTS empleados (
    id_empleados INT AUTO_INCREMENT PRIMARY KEY,
    emp_nombre VARCHAR(50) NOT NULL,
    emp_apaterno VARCHAR(50) NOT NULL,
    emp_amaterno VARCHAR(50),
    emp_telefono VARCHAR(15),
    emp_direccion TEXT,
    emp_rol ENUM('Administrador', 'Vendedor', 'Mecanico') NOT NULL,
    emp_usuario VARCHAR(20) UNIQUE NOT NULL,
    emp_password VARCHAR(255) NOT NULL,
    emp_estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario (emp_usuario),
    INDEX idx_estado (emp_estado)
);

-- Tabla de Usuarios (tabla normalizada para autenticación)
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('Empleado', 'Admin', 'Cliente') DEFAULT 'Cliente',
    id_empleado INT UNSIGNED NULL,
    id_cliente INT UNSIGNED NULL,
    estado ENUM('Activo', 'Inactivo', 'Bloqueado') DEFAULT 'Activo',
    email_verified_at TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_correo (correo),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_estado (estado),
    UNIQUE KEY unique_email (correo)
);

-- Tabla de Password Resets (recuperación de contraseña)
CREATE TABLE IF NOT EXISTS password_resets (
    id_reset BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    correo VARCHAR(100) NOT NULL,
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    utilizado BOOLEAN DEFAULT FALSE,
    fecha_uso TIMESTAMP NULL,
    ip_solicitud VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_id_usuario (id_usuario),
    INDEX idx_token (token),
    INDEX idx_correo (correo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Productos
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    pro_codigo VARCHAR(50) UNIQUE NOT NULL,
    pro_nombre VARCHAR(100) NOT NULL,
    pro_tipo VARCHAR(50),
    pro_marca VARCHAR(50),
    pro_descripcion TEXT,
    pro_precio_venta DECIMAL(10,2) NOT NULL,
    pro_stock INT DEFAULT 0,
    id_categoria INT,
    id_proveedor INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    INDEX idx_codigo (pro_codigo),
    INDEX idx_stock (pro_stock)
);

-- Tabla de Control de Caja
CREATE TABLE IF NOT EXISTS control_cajas (
    id_caja INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT,
    fecha_apertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    monto_inicial DECIMAL(10,2) NOT NULL,
    fecha_cierre DATETIME,
    monto_final_esperado DECIMAL(10,2),
    monto_real_cierre DECIMAL(10,2),
    estado ENUM('Abierta', 'Cerrada') DEFAULT 'Abierta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados),
    INDEX idx_estado (estado)
);

-- Tabla de Ventas
CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NULL,
    id_empleado INT,
    id_caja INT,
    ven_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ven_total DECIMAL(10,2) NOT NULL,
    tipo_pago ENUM('Efectivo', 'Tarjeta', 'Transferencia'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados),
    FOREIGN KEY (id_caja) REFERENCES control_cajas(id_caja),
    INDEX idx_fecha (ven_fecha)
);

-- Tabla de Detalles de Ventas
CREATE TABLE IF NOT EXISTS detalle_ventas (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT,
    id_producto INT,
    det_cantidad INT NOT NULL,
    det_precio_unitario DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Tabla de Compras
CREATE TABLE IF NOT EXISTS compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT,
    id_empleado INT,
    com_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    com_total DECIMAL(10,2),
    com_factura_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados),
    INDEX idx_factura (com_factura_no)
);

-- Tabla de Detalles de Compras
CREATE TABLE IF NOT EXISTS detalle_compras (
    id_det_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT,
    id_producto INT,
    det_cantidad INT NOT NULL,
    det_costo_unitario DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_compra) REFERENCES compras(id_compra),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Tabla de Cotizaciones
CREATE TABLE IF NOT EXISTS cotizaciones (
    id_cotizacion INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_empleado INT,
    cot_fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    cot_vigencia_dias INT DEFAULT 15,
    cot_total DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados),
    INDEX idx_fecha (cot_fecha)
);

-- Tabla de Citas
CREATE TABLE IF NOT EXISTS citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_empleado INT,
    cita_fecha_programada DATETIME NOT NULL,
    cita_motivo VARCHAR(255),
    cita_estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Realizada') DEFAULT 'Pendiente',
    cita_notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleados),
    INDEX idx_estado (cita_estado),
    INDEX idx_fecha (cita_fecha_programada)
);

-- Tabla de Servicios
CREATE TABLE IF NOT EXISTS servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    ser_nombre VARCHAR(100) NOT NULL,
    ser_descripcion TEXT,
    ser_precio_mano_obra DECIMAL(10,2) NOT NULL,
    id_categoria INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    INDEX idx_nombre (ser_nombre)
);

-- Tabla de Detalles de Citas y Servicios
CREATE TABLE IF NOT EXISTS detalle_cita_servicios (
    id_det_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_cita INT,
    id_servicio INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cita) REFERENCES citas(id_cita),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
);

-- Tabla de Mantenimiento
CREATE TABLE IF NOT EXISTS mantenimientos (
    id_mantenimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    id_mecanico INT,
    id_cita INT NULL,
    moto_modelo VARCHAR(100),
    moto_llegada_descripcion TEXT,
    trabajo_realizado TEXT,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_termino DATETIME,
    mantenimiento_total DECIMAL(10,2),
    estado_servicio ENUM('En Proceso', 'Terminado', 'Entregado') DEFAULT 'En Proceso',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_mecanico) REFERENCES empleados(id_empleados),
    FOREIGN KEY (id_cita) REFERENCES citas(id_cita),
    INDEX idx_estado (estado_servicio)
);

-- Tabla de Detalles de Mantenimiento e Insumos
CREATE TABLE IF NOT EXISTS detalle_mantenimiento_insumos (
    id_det_mant INT AUTO_INCREMENT PRIMARY KEY,
    id_mantenimiento INT,
    id_producto INT,
    insumo_cantidad INT,
    insumo_precio_unitario DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mantenimiento) REFERENCES mantenimientos(id_mantenimiento),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Tabla de Detalles de Mantenimiento y Servicios
CREATE TABLE IF NOT EXISTS detalle_mantenimiento_servicios (
    id_det_mant_ser INT AUTO_INCREMENT PRIMARY KEY,
    id_mantenimiento INT,
    id_servicio INT,
    precio_aplicado DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mantenimiento) REFERENCES mantenimientos(id_mantenimiento),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
);

-- Tabla de Detalle de Cotizaciones
CREATE TABLE IF NOT EXISTS detalle_cotizaciones (
    id_det_cot INT AUTO_INCREMENT PRIMARY KEY,
    id_cotizacion INT,
    id_producto INT,
    id_servicio INT NULL,
    det_cantidad INT,
    det_precio_unitario DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id_cotizacion),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
);

-- Crear índices adicionales para optimización
ALTER TABLE usuarios ADD INDEX idx_tipo_usuario_estado (tipo_usuario, estado);
ALTER TABLE password_resets ADD INDEX idx_utilizado_expiracion (utilizado, fecha_expiracion);
ALTER TABLE citas ADD INDEX idx_cliente_estado (id_cliente, cita_estado);
ALTER TABLE mantenimientos ADD INDEX idx_mecanico_estado (id_mecanico, estado_servicio);
ALTER TABLE ventas ADD INDEX idx_empleado_fecha (id_empleado, ven_fecha);
