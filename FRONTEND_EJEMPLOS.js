// Ejemplos de integración Frontend con JHP API de Autenticación
// Estos ejemplos usan Fetch API (vanilla JavaScript)

const API_BASE_URL = 'http://localhost:8000/api';

// ============================================
// 1. FUNCIONES DE AUTENTICACIÓN
// ============================================

/**
 * Login
 */
async function login(correo, password) {
  try {
    const response = await fetch(`${API_BASE_URL}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        correo,
        password,
      }),
    });

    const data = await response.json();

    if (data.success) {
      // Guardar token en localStorage
      localStorage.setItem('authToken', data.data.token);
      localStorage.setItem('usuario', JSON.stringify(data.data.usuario));
      
      console.log('✅ Login exitoso');
      return data.data;
    } else {
      console.error('❌ Error en login:', data.message);
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

/**
 * Registrarse
 */
async function register(correo, password, passwordConfirmation, nombre) {  try {
    const response = await fetch(`${API_BASE_URL}/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        correo,
        password,
        password_confirmation: passwordConfirmation,
        nombre,
      }),
    });

    const data = await response.json();

    if (data.success) {
      localStorage.setItem('authToken', data.data.token);
      localStorage.setItem('usuario', JSON.stringify(data.data.usuario));
      
      console.log('✅ Registro exitoso');
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

/**
 * Logout
 */
async function logout() {
  try {
    const token = localStorage.getItem('authToken');
    
    const response = await fetch(`${API_BASE_URL}/auth/logout`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    const data = await response.json();

    // Limpiar localStorage
    localStorage.removeItem('authToken');
    localStorage.removeItem('usuario');

    console.log('✅ Logout exitoso');
    return data;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

/**
 * Obtener perfil del usuario actual
 */
async function getProfile() {
  try {
    const token = localStorage.getItem('authToken');
    
    const response = await fetch(`${API_BASE_URL}/auth/me`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    const data = await response.json();

    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// ============================================
// 2. FUNCIONES DE RECUPERACIÓN DE CONTRASEÑA
// ============================================

/**
 * Solicitar recuperación de contraseña
 */
async function requestPasswordReset(correo) {
  try {
    const response = await fetch(`${API_BASE_URL}/password-reset/request`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ correo }),
    });

    const data = await response.json();

    if (data.success) {
      console.log('✅ Email de recuperación enviado');
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

/**
 * Validar token de recuperación
 */
async function validateResetToken(token) {
  try {
    const response = await fetch(`${API_BASE_URL}/password-reset/validate-token`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ token }),
    });

    const data = await response.json();

    if (data.success) {
      console.log('✅ Token válido');
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

/**
 * Resetear contraseña con token
 */
async function resetPassword(token, password, passwordConfirmation) {
  try {
    const response = await fetch(`${API_BASE_URL}/password-reset/reset`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token,
        password,
        password_confirmation: passwordConfirmation,
      }),
    });

    const data = await response.json();

    if (data.success) {
      console.log('✅ Contraseña actualizada');
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

/**
 * Cambiar contraseña (usuario autenticado)
 */
async function changePassword(passwordActual, passwordNueva, passwordNuevaConfirmation) {
  try {
    const token = localStorage.getItem('authToken');
    
    const response = await fetch(`${API_BASE_URL}/password-reset/change`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        password_actual: passwordActual,
        password_nueva: passwordNueva,
        password_nueva_confirmation: passwordNuevaConfirmation,
      }),
    });

    const data = await response.json();

    if (data.success) {
      console.log('✅ Contraseña cambiada exitosamente');
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

// ============================================
// 3. FUNCIONES DE UTILIDAD
// ============================================

/**
 * Obtener token del localStorage
 */
function getAuthToken() {
  return localStorage.getItem('authToken');
}

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
  return !!localStorage.getItem('authToken');
}

/**
 * Obtener usuario del localStorage
 */
function getCurrentUser() {
  const userStr = localStorage.getItem('usuario');
  return userStr ? JSON.parse(userStr) : null;
}

/**
 * Hacer petición con token automático
 */
async function fetchWithAuth(url, options = {}) {
  const token = getAuthToken();
  
  if (!token) {
    throw new Error('No autenticado');
  }

  const headers = {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    ...options.headers,
  };

  const response = await fetch(url, {
    ...options,
    headers,
  });

  return response.json();
}

// ============================================
// 4. EJEMPLOS DE USO
// ============================================

/*
// EJEMPLO 1: Login
await login('admin@jhpapi.com', 'Admin@123');

// EJEMPLO 2: Obtener perfil
const profile = await getProfile();
console.log(profile);

// EJEMPLO 3: Registrarse
await register('nuevo@jhpapi.com', 'Pass@123', 'Pass@123', 'Juan');

// EJEMPLO 4: Solicitar recuperación
await requestPasswordReset('usuario@jhpapi.com');

// EJEMPLO 5: Validar token (después de hacer clic en email)
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
const validacion = await validateResetToken(token);

// EJEMPLO 6: Resetear contraseña
await resetPassword(token, 'NuevaPass@123', 'NuevaPass@123');

// EJEMPLO 7: Cambiar contraseña (estando logueado)
await changePassword('ContraseñaActual', 'NuevaPass@123', 'NuevaPass@123');

// EJEMPLO 8: Logout
await logout();
*/

// ============================================
// 5. COMPONENTE VUE 3 (EJEMPLO)
// ============================================

/*
<template>
  <div>
    <div v-if="!isLoggedIn">
      <!-- Formulario Login -->
      <form @submit.prevent="handleLogin">
        <input v-model="form.correo" type="email" placeholder="Correo" required>
        <input v-model="form.password" type="password" placeholder="Contraseña" required>
        <button type="submit" :disabled="loading">Iniciar Sesión</button>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>

    <div v-else>
      <!-- Panel Usuario Logueado -->
      <h1>Bienvenido, {{ usuario?.correo }}</h1>
      <button @click="handleLogout">Cerrar Sesión</button>
      <button @click="showChangePassword = true">Cambiar Contraseña</button>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div v-if="showChangePassword" class="modal">
      <form @submit.prevent="handleChangePassword">
        <input v-model="passwordForm.actual" type="password" placeholder="Contraseña actual" required>
        <input v-model="passwordForm.nueva" type="password" placeholder="Nueva contraseña" required>
        <input v-model="passwordForm.confirmacion" type="password" placeholder="Confirmar contraseña" required>
        <button type="submit">Cambiar</button>
        <button type="button" @click="showChangePassword = false">Cancelar</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const isLoggedIn = computed(() => localStorage.getItem('authToken') !== null);
const usuario = computed(() => {
  const u = localStorage.getItem('usuario');
  return u ? JSON.parse(u) : null;
});

const form = ref({ correo: '', password: '' });
const loading = ref(false);
const error = ref('');
const showChangePassword = ref(false);
const passwordForm = ref({ actual: '', nueva: '', confirmacion: '' });

async function handleLogin() {
  loading.value = true;
  error.value = '';
  try {
    await login(form.correo.value, form.password.value);
    form.value = { correo: '', password: '' };
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
}

async function handleLogout() {
  await logout();
}

async function handleChangePassword() {
  try {
    await changePassword(
      passwordForm.value.actual,
      passwordForm.value.nueva,
      passwordForm.value.confirmacion
    );
    alert('Contraseña cambiada exitosamente');
    showChangePassword.value = false;
    passwordForm.value = { actual: '', nueva: '', confirmacion: '' };
  } catch (e) {
    error.value = e.message;
  }
}
</script>
*/

// ============================================
// 6. COMPONENTE REACT (EJEMPLO)
// ============================================

/*
import React, { useState, useEffect } from 'react';

function AuthComponent() {
  const [isLoggedIn, setIsLoggedIn] = useState(!!localStorage.getItem('authToken'));
  const [usuario, setUsuario] = useState(null);
  const [form, setForm] = useState({ correo: '', password: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const user = localStorage.getItem('usuario');
    if (user) {
      setUsuario(JSON.parse(user));
    }
  }, [isLoggedIn]);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    
    try {
      await login(form.correo, form.password);
      setIsLoggedIn(true);
      setForm({ correo: '', password: '' });
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    await logout();
    setIsLoggedIn(false);
    setUsuario(null);
  };

  if (isLoggedIn && usuario) {
    return (
      <div>
        <h1>Bienvenido, {usuario.correo}</h1>
        <button onClick={handleLogout}>Cerrar Sesión</button>
      </div>
    );
  }

  return (
    <form onSubmit={handleLogin}>
      <input
        type="email"
        value={form.correo}
        onChange={(e) => setForm({ ...form, correo: e.target.value })}
        placeholder="Correo"
        required
      />
      <input
        type="password"
        value={form.password}
        onChange={(e) => setForm({ ...form, password: e.target.value })}
        placeholder="Contraseña"
        required
      />
      <button type="submit" disabled={loading}>
        {loading ? 'Iniciando...' : 'Iniciar Sesión'}
      </button>
      {error && <p style={{ color: 'red' }}>{error}</p>}
    </form>
  );
}

export default AuthComponent;
*/
