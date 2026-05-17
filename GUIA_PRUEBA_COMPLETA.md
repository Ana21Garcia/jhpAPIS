# Guía Completa de Prueba - Sistema de Recuperación de Contraseña

## 🎯 Objetivo
Probar el flujo completo de recuperación de contraseña paso a paso.

---

## 📋 Requisitos

- ✅ Laravel instalado y ejecutándose en `http://localhost:8000`
- ✅ Base de datos `motos` creada
- ✅ Migraciones ejecutadas: `php artisan migrate`
- ✅ Usuarios de prueba creados: `php artisan db:seed`
- ✅ Email configurado en `.env`
- ✅ Postman o cURL instalado

---

## 🔄 Flujo de Prueba Completo

### PASO 1: Obtener un token de login

Primero, vamos a autenticar un usuario para probar que todo funciona.

**Usar Postman:**
```
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "correo": "admin@jhpapi.com",
  "password": "Admin@123"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "data": {
    "usuario": {
      "id_usuario": 1,
      "correo": "admin@jhpapi.com",
      "tipo_usuario": "Admin",
      ...
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  }
}
```

**Guardar el token para usar en pruebas futuras**

---

### PASO 2: Solicitar recuperación de contraseña

Simularemos que un usuario olvidó su contraseña.

**Usar Postman:**
```
POST http://localhost:8000/api/password-reset/request
Content-Type: application/json

{
  "correo": "prueba@jhpapi.com"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Si el correo está registrado, recibirá instrucciones de recuperación",
  "debug_info": {
    "token_created": true,
    "token_expires_in_hours": 24
  }
}
```

**✅ Verificar:** Revisa tu email en `anne2jhp@gmail.com` (o donde esté configurado MAIL_FROM_ADDRESS)

---

### PASO 3: Obtener el token de recuperación de la base de datos

Como estamos en desarrollo, podemos ver el token directamente:

**En MySQL o phpMyAdmin:**
```sql
SELECT * FROM password_resets 
WHERE correo = 'prueba@jhpapi.com' 
ORDER BY fecha_solicitud DESC 
LIMIT 1;
```

O usando Tinker:
```bash
php artisan tinker

>>> $reset = App\Models\PasswordReset::where('correo', 'prueba@jhpapi.com')->latest()->first();
>>> $reset->token
```

**Copiar el token** para el siguiente paso

---

### PASO 4: Validar el token

Verificamos que el token sea válido antes de permitir que el usuario cambie su contraseña.

**Usar Postman:**
```
POST http://localhost:8000/api/password-reset/validate-token
Content-Type: application/json

{
  "token": "PEGA_EL_TOKEN_AQUI"
}
```

**Respuesta esperada (si es válido):**
```json
{
  "success": true,
  "message": "Token válido",
  "data": {
    "correo": "prueba@jhpapi.com",
    "fecha_expiracion": "2026-05-18T10:30:00"
  }
}
```

**Respuesta esperada (si es inválido/expirado):**
```json
{
  "success": false,
  "message": "Token inválido o expirado"
}
```

---

### PASO 5: Resetear la contraseña

Ahora cambiaremos la contraseña del usuario con el token válido.

**Usar Postman:**
```
POST http://localhost:8000/api/password-reset/reset
Content-Type: application/json

{
  "token": "PEGA_EL_TOKEN_AQUI",
  "password": "NuevaPass@123",
  "password_confirmation": "NuevaPass@123"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Contraseña actualizada exitosamente"
}
```

---

### PASO 6: Probar login con nueva contraseña

Verificamos que la contraseña nueva funciona.

**Usar Postman:**
```
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "correo": "prueba@jhpapi.com",
  "password": "NuevaPass@123"
}
```

**Resultado esperado:** ✅ Login exitoso

---

### PASO 7: Intentar reutilizar el token (debe fallar)

Una característica de seguridad es que un token solo se puede usar una vez.

**Usar Postman (usar el mismo token del PASO 5):**
```
POST http://localhost:8000/api/password-reset/reset
Content-Type: application/json

{
  "token": "PEGA_EL_MISMO_TOKEN_ANTERIOR",
  "password": "OtraPass@123",
  "password_confirmation": "OtraPass@123"
}
```

**Respuesta esperada:**
```json
{
  "success": false,
  "message": "Token inválido o expirado"
}
```

✅ **Esto es correcto - la seguridad funciona**

---

## 🧪 Pruebas Adicionales

### Prueba A: Validar errores de validación

**Solicitud:**
```
POST http://localhost:8000/api/password-reset/reset
Content-Type: application/json

{
  "token": "validtoken",
  "password": "123",
  "password_confirmation": "456"
}
```

**Respuesta esperada:**
```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "password": [
      "La contraseña debe tener al menos 8 caracteres",
      "Las contraseñas no coinciden"
    ]
  }
}
```

### Prueba B: Correo inválido en request

**Solicitud:**
```
POST http://localhost:8000/api/password-reset/request
Content-Type: application/json

{
  "correo": "correo-invalido"
}
```

**Respuesta esperada:**
```json
{
  "success": false,
  "message": "Validación fallida",
  "errors": {
    "correo": [
      "El correo debe ser válido"
    ]
  }
}
```

### Prueba C: Cambiar contraseña estando autenticado

**Solicitud:**
```
POST http://localhost:8000/api/password-reset/change
Authorization: Bearer {tu_token_aqui}
Content-Type: application/json

{
  "password_actual": "NuevaPass@123",
  "password_nueva": "FinalPass@123",
  "password_nueva_confirmation": "FinalPass@123"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Contraseña actualizada exitosamente"
}
```

---

## 🔍 Verificar en la Base de Datos

### Ver intentos de recuperación:
```sql
SELECT * FROM password_resets ORDER BY fecha_solicitud DESC;
```

### Ver usuarios y sus últimos accesos:
```sql
SELECT id_usuario, correo, estado, ultimo_acceso 
FROM usuarios;
```

### Ver tokens utilizados vs no utilizados:
```sql
SELECT token, correo, utilizado, fecha_expiracion 
FROM password_resets 
WHERE utilizado = true;
```

---

## 📊 Casos de Prueba Esperados

| # | Caso | Entrada | Resultado Esperado |
|----|------|---------|-------------------|
| 1 | Login correcto | correo + password correctos | ✅ Token generado |
| 2 | Request recuperación | correo válido | ✅ Email enviado (simulado) |
| 3 | Validar token válido | token de recuperación válido | ✅ Token aceptado |
| 4 | Resetear con token válido | token + password nueva | ✅ Contraseña cambiada |
| 5 | Reutilizar token | mismo token 2ª vez | ❌ Token rechazado |
| 6 | Token expirado | token > 24 horas | ❌ Token rechazado |
| 7 | Cambiar password auth | password actual + nueva | ✅ Contraseña cambiada |
| 8 | Password no coincide | password ≠ confirmation | ❌ Validación falla |
| 9 | Email no registrado | correo inexistente | ✅ Respuesta neutral |
| 10 | Sin autorización | endpoint protegido sin token | ❌ Error 401 |

---

## 🐛 Debugging

### Ver logs de Laravel:
```bash
# En tiempo real
tail -f storage/logs/laravel.log

# O revisar el archivo completo
cat storage/logs/laravel.log
```

### Ver emails enviados (en desarrollo):
```bash
# Si usas MailHog o similar
http://localhost:1025
```

### Usar Tinker para debugging:
```bash
php artisan tinker

# Ver último reset
>>> App\Models\PasswordReset::latest()->first();

# Ver usuario
>>> App\Models\Usuario::where('correo', 'prueba@jhpapi.com')->first();

# Crear reset manual
>>> App\Models\PasswordReset::crearSolicitud(1, 'prueba@jhpapi.com')
```

---

## 📞 Solucionar Problemas

### Email no se envía
```
1. Verifica MAIL_* en .env
2. Revisa storage/logs/laravel.log
3. Prueba: php artisan tinker -> Mail::raw('test', fn($m) => $m->to('email@gmail.com'))
```

### Token no funciona
```
1. Verifica que no esté expirado (24 horas)
2. Verifica que no esté marcado como utilizado
3. Revisa la BD: SELECT * FROM password_resets WHERE token = 'xxx';
```

### 401 Unauthorized
```
1. Verifica que el token se envía correctamente
2. Formato: Authorization: Bearer {token}
3. Verifica que Sanctum esté publicado
```

### CORS Error
```
# En .env, añade:
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,127.0.0.1:3000
```

---

## ✅ Checklist Final

- [ ] Composer actualizado: `composer install`
- [ ] Migraciones ejecutadas: `php artisan migrate`
- [ ] Seeders ejecutados: `php artisan db:seed`
- [ ] .env configurado con email
- [ ] Servidor ejecutándose: `php artisan serve`
- [ ] Login funciona
- [ ] Request recuperación funciona
- [ ] Email se envía (o simula)
- [ ] Token valida correctamente
- [ ] Contraseña se resetea
- [ ] Login con nueva password funciona
- [ ] Token no se reutiliza

---

## 📝 Notas

- En desarrollo, puedes ver tokens en la BD directamente
- Los tokens expiran en 24 horas automáticamente
- Los emails en desarrollo pueden ir a la carpeta especificada en MAIL_MAILER
- La API devuelve respuestas coherentes aunque el usuario no exista (seguridad)

---

## 🎉 ¡Listo!

Si todos los pasos funcionan correctamente, tu sistema de autenticación y recuperación de contraseña está completamente funcional.

**Próximos pasos:**
1. Conectar frontend con la API
2. Implementar UI para login
3. Implementar UI para recuperación
4. Probar en producción

---

**Documentación:** AUTENTICACION_README.md
**Ejemplos Frontend:** FRONTEND_EJEMPLOS.js
**Contacto:** anne2jhp@gmail.com
