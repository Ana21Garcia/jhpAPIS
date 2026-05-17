# Integración de Autenticación y Recuperación de Contraseña

## 📋 Descripción General

Este documento describe la integración de un sistema completo de autenticación y recuperación de contraseña usando Laravel y Supabase para la API JHP.

### Características principales:
- ✅ Login con correo y contraseña
- ✅ Registro de usuarios
- ✅ Recuperación de contraseña por email
- ✅ Cambio de contraseña (usuario autenticado)
- ✅ Autenticación basada en tokens (Laravel Sanctum)
- ✅ Integración con Supabase JWT (opcional)
- ✅ Gestión de usuarios (admin)
- ✅ Auditoria de intentos de recuperación

---

## 🚀 Pasos de Instalación

### 1. Instalar dependencias

```bash
# Navega al directorio del proyecto
cd c:\xampp\htdocs\jhpAPI

# Instala Laravel Sanctum
composer require laravel/sanctum

# Publica la configuración
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 2. Ejecutar migraciones

```bash
# Ejecuta todas las migraciones
php artisan migrate

# O si usas una base de datos específica
php artisan migrate --database=mysql

# Si necesitas resetear la base de datos (CUIDADO - elimina datos)
php artisan migrate:refresh
```

### 3. Configurar variables de entorno

Edita el archivo `.env` y asegúrate de que estas variables estén configuradas:

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motos
DB_USERNAME=root
DB_PASSWORD=

# Email (para recuperación de contraseña)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=anne2jhp@gmail.com
MAIL_PASSWORD=tu_password_app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=anne2jhp@gmail.com
MAIL_FROM_NAME="JHP API"

# Frontend URL (para links en emails)
FRONTEND_URL=http://localhost:3000
# O en producción:
# FRONTEND_URL=https://tudominio.com

# Sanctum (API tokens)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
SANCTUM_MIDDLEWARE_SHOULD_CHECK_AGAINST_CSRF_TOKEN=true
```

### 4. Limpiar caché

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📡 Endpoints de API

### Autenticación (Públicos)

#### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "correo": "usuario@example.com",
  "password": "tu_contraseña"
}

Response: 200 OK
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "data": {
    "usuario": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  }
}
```

#### Registrarse
```
POST /api/auth/register
Content-Type: application/json

{
  "correo": "nuevo@example.com",
  "password": "contraseña_segura_8_chars",
  "password_confirmation": "contraseña_segura_8_chars",
  "nombre": "Juan Pérez"
}

Response: 201 Created
```

#### Logout (Requiere token)
```
POST /api/auth/logout
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "message": "Cierre de sesión exitoso"
}
```

#### Ver perfil (Requiere token)
```
GET /api/auth/me
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "data": { ... }
}
```

### Recuperación de Contraseña (Públicos)

#### Solicitar recuperación
```
POST /api/password-reset/request
Content-Type: application/json

{
  "correo": "usuario@example.com"
}

Response: 200 OK
{
  "success": true,
  "message": "Si el correo está registrado, recibirá instrucciones de recuperación"
}
```

#### Validar token
```
POST /api/password-reset/validate-token
Content-Type: application/json

{
  "token": "hash_del_token_aquí"
}

Response: 200 OK
{
  "success": true,
  "message": "Token válido",
  "data": {
    "correo": "usuario@example.com",
    "fecha_expiracion": "2026-05-18T10:30:00"
  }
}
```

#### Resetear contraseña
```
POST /api/password-reset/reset
Content-Type: application/json

{
  "token": "hash_del_token_aquí",
  "password": "nueva_contraseña_8_chars",
  "password_confirmation": "nueva_contraseña_8_chars"
}

Response: 200 OK
{
  "success": true,
  "message": "Contraseña actualizada exitosamente"
}
```

#### Cambiar contraseña (Requiere token)
```
POST /api/password-reset/change
Authorization: Bearer {token}
Content-Type: application/json

{
  "password_actual": "contraseña_actual",
  "password_nueva": "nueva_contraseña_8_chars",
  "password_nueva_confirmation": "nueva_contraseña_8_chars"
}

Response: 200 OK
```

### Gestión de Usuarios (Protegido - Requiere token)

#### Listar usuarios (Solo admin)
```
GET /api/usuarios
Authorization: Bearer {token}

Response: 200 OK
{
  "success": true,
  "data": { ... }
}
```

#### Ver usuario específico
```
GET /api/usuarios/{id}
Authorization: Bearer {token}
```

#### Crear usuario (Solo admin)
```
POST /api/usuarios
Authorization: Bearer {token}
Content-Type: application/json

{
  "correo": "nuevo@example.com",
  "password": "contraseña_8_chars",
  "tipo_usuario": "Empleado",
  "estado": "Activo",
  "id_empleado": 1
}
```

#### Actualizar usuario
```
PUT /api/usuarios/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "correo": "actualizado@example.com",
  "estado": "Activo"
}
```

#### Cambiar estado (Solo admin)
```
PATCH /api/usuarios/{id}/estado
Authorization: Bearer {token}
Content-Type: application/json

{
  "estado": "Bloqueado"
}
```

#### Eliminar usuario (Solo admin)
```
DELETE /api/usuarios/{id}
Authorization: Bearer {token}
```

---

## 🔐 Flujo de Recuperación de Contraseña

### 1. Usuario solicita recuperación
```
Cliente                    API                    Email
   |                         |                       |
   ├─ POST /password-reset/request ──────────────>  |
   |                         |                       |
   |                         ├─ Generar token       |
   |                         ├─ Guardar en BD       |
   |                         └─ Enviar email ──────>|
   |                         |<─ Email enviado      |
   |<─ Respuesta OK ────────┤                       |
```

### 2. Usuario recibe email y hace clic
- Email contiene link con token: `https://frontend.com/recuperar?token=hash`

### 3. Frontend valida token
```
Frontend                   API
   |                        |
   ├─ POST /validate-token ─┤
   |                        ├─ Validar token
   |<─ Token válido ────────┤
```

### 4. Usuario ingresa nueva contraseña
```
Frontend                   API                    BD
   |                        |                      |
   ├─ POST /reset ─────────>|                      |
   |                        ├─ Validar token       |
   |                        ├─ Hash nueva pass     |
   |                        └─ Actualizar BD ─────>|
   |<─ Éxito ──────────────┤                       |
```

---

## 📧 Configuración de Email (Gmail)

### Obtener contraseña de aplicación

1. Ve a https://myaccount.google.com/security
2. Activa autenticación de dos pasos
3. Ve a "Contraseñas de aplicación"
4. Genera una contraseña para "Correo" y "Windows"
5. Copia la contraseña de 16 caracteres
6. Pégala en `.env` como `MAIL_PASSWORD`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=anne2jhp@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx  # 16 caracteres
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=anne2jhp@gmail.com
MAIL_FROM_NAME="JHP API"
```

---

## 🔐 Integración con Supabase (Opcional)

Si quieres usar Supabase JWT directamente:

### Instalar paquete JWT
```bash
composer require firebase/php-jwt
```

### Usar token Supabase
```php
// En AuthController
use Firebase\JWT\JWT;

$payload = [
    "iss" => "https://project_id.supabase.co/auth/v1",
    "exp" => time() + 3600,
    "sub" => $usuario->id_usuario,
    "role" => "authenticated",
    "email" => $usuario->correo,
    "phone" => "+15552368"
];

$token = JWT::encode($payload, env('SUPABASE_JWT_SECRET'), 'HS256');
```

---

## 🗄️ Estructura de Tablas

### Tabla: `usuarios`
```sql
id_usuario (PK)
correo (UNIQUE)
password
tipo_usuario (Empleado | Admin | Cliente)
id_empleado (FK)
id_cliente (FK)
estado (Activo | Inactivo | Bloqueado)
email_verified_at
ultimo_acceso
created_at
updated_at
```

### Tabla: `password_resets`
```sql
id_reset (PK)
id_usuario (FK)
token (UNIQUE)
correo
fecha_solicitud
fecha_expiracion
utilizado (BOOLEAN)
fecha_uso
ip_solicitud
user_agent
created_at
updated_at
```

---

## 🧪 Pruebas

### Usando cURL

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "correo": "admin@example.com",
    "password": "password123"
  }'

# Solicitar recuperación
curl -X POST http://localhost:8000/api/password-reset/request \
  -H "Content-Type: application/json" \
  -d '{
    "correo": "usuario@example.com"
  }'
```

### Usando Postman

1. Importa la colección de endpoints
2. Configura variables de entorno:
   - `base_url` = `http://localhost:8000`
   - `token` = (se rellena después del login)
3. Prueba cada endpoint

---

## 🐛 Solucionar Problemas

### Error: "SQLSTATE[HY000]: General error: 1030"
- Aumenta `max_allowed_packet` en MySQL
- Edit `my.ini`: `max_allowed_packet=256M`

### Error: "Class 'Illuminate\Auth\Middleware\Authenticate' not found"
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Email no se envía
- Verifica credenciales en `.env`
- Revisa logs: `storage/logs/laravel.log`
- Asegúrate que "Aplicaciones menos seguras" esté habilitado en Gmail

### Token expirado
- Los tokens de recuperación vencen en 24 horas
- Los tokens de sesión vencen según configuración de Sanctum

---

## 📚 Archivos Creados

```
app/
├── Http/Controllers/API/
│   ├── AuthController.php          (Login, Register, Logout)
│   ├── PasswordResetController.php (Recuperación)
│   └── UsuarioController.php       (Gestión de usuarios)
├── Models/
│   ├── Usuario.php                 (Usuario modelo)
│   └── PasswordReset.php           (PasswordReset modelo)
└── Mail/
    └── PasswordResetMail.php       (Email mailable)

database/
├── migrations/
│   ├── 2026_05_17_000001_create_usuarios_table.php
│   └── 2026_05_17_000002_create_password_resets_table.php
└── sql/
    └── schema_motos.sql            (Script SQL completo)

resources/views/emails/
└── password-reset.blade.php        (Vista de email)

routes/
└── api.php                         (Rutas API actualizadas)
```

---

## 📞 Soporte

Para preguntas o problemas:
- Email: anne2jhp@gmail.com
- Documentación Laravel: https://laravel.com/docs
- Documentación Supabase: https://supabase.com/docs

---

**Última actualización:** 17 de mayo de 2026
**Versión:** 1.0.0
