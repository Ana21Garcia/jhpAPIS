# Guía Rápida de Instalación - Sistema de Autenticación JHP API

## 🚀 Instalación en 5 pasos

### Paso 1: Navegar al directorio del proyecto
```bash
cd c:\xampp\htdocs\jhpAPI
```

### Paso 2: Instalar Laravel Sanctum
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Paso 3: Configurar el archivo .env
```bash
# Copiar el archivo de ejemplo
copy .env.example .env

# Generar APP_KEY (si aún no lo tienes)
php artisan key:generate
```

**Edita `.env` y configura:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motos
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=anne2jhp@gmail.com
MAIL_PASSWORD=tu_contrasena_app_16_caracteres
MAIL_FROM_ADDRESS=anne2jhp@gmail.com

FRONTEND_URL=http://localhost:3000
```

### Paso 4: Crear la base de datos y ejecutar migraciones
```bash
# Crear la base de datos 'motos' en MySQL
mysql -u root -e "CREATE DATABASE motos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ejecutar todas las migraciones
php artisan migrate

# (Opcional) Llenar con datos de prueba
php artisan db:seed
```

### Paso 5: Iniciar el servidor
```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

---

## 📱 Probar la API

### Test 1: Login
```bash
curl -X POST http://localhost:8000/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"correo\":\"admin@jhpapi.com\",\"password\":\"Admin@123\"}"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "data": {
    "usuario": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  }
}
```

### Test 2: Solicitar recuperación de contraseña
```bash
curl -X POST http://localhost:8000/api/password-reset/request ^
  -H "Content-Type: application/json" ^
  -d "{\"correo\":\"prueba@jhpapi.com\"}"
```

### Test 3: Obtener perfil (usando token)
```bash
curl -X GET http://localhost:8000/api/auth/me ^
  -H "Authorization: Bearer AQUI_VA_TU_TOKEN"
```

---

## 🗄️ Script SQL directo (Alternativa)

Si prefieres crear las tablas directamente con SQL:

```bash
# Conectarse a MySQL
mysql -u root -p

# En el prompt de MySQL:
mysql> CREATE DATABASE motos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> USE motos;
mysql> source database/sql/schema_motos.sql;
```

---

## ✅ Checklist de Verificación

- [ ] Composer instalado: `composer --version`
- [ ] PHP 8.2+: `php --version`
- [ ] MySQL corriendo en puerto 3306
- [ ] `php artisan migrate` ejecutado sin errores
- [ ] Email configurado y probado
- [ ] Token JWT generado: `php artisan jwt:secret` (si usas JWT)
- [ ] Usuarios de prueba creados: `php artisan db:seed`
- [ ] Servidor Laravel ejecutándose: `php artisan serve`

---

## 🔐 Credenciales de Prueba (después de db:seed)

| Tipo | Email | Contraseña |
|------|-------|-----------|
| Admin | admin@jhpapi.com | Admin@123 |
| Empleado | empleado@jhpapi.com | Empleado@123 |
| Cliente | cliente@jhpapi.com | Cliente@123 |
| Prueba | prueba@jhpapi.com | Prueba@123 |

---

## 🐛 Solucionar Problemas Comunes

### Error: "Class 'Laravel\Sanctum\SanctumServiceProvider' not found"
```bash
composer dump-autoload
php artisan config:cache
```

### Error: "SQLSTATE[HY000]: General error: 1030"
```sql
-- En MySQL, aumentar el límite de paquetes
SET GLOBAL max_allowed_packet = 256 * 1024 * 1024;
```

### El email no se envía
1. Verifica que `MAIL_USERNAME` y `MAIL_PASSWORD` sean correctos en `.env`
2. Revisa logs: `storage/logs/laravel.log`
3. Asegúrate que Gmail tenga habilitadas "Aplicaciones menos seguras"

### Token expirado
- Los tokens expiran según `SANCTUM_STATEFUL_DOMAINS` en `.env`
- Ajusta la configuración si es necesario

---

## 📚 Documentación Completa

Para más detalles, consulta: `AUTENTICACION_README.md`

---

## 📞 Contacto

Email: anne2jhp@gmail.com
