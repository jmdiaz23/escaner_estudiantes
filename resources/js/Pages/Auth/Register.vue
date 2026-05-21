<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registro - LICEO SAMARIO" />

    <section class="auth-container">
        <!-- Círculos decorativos para el fondo -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>

        <div class="login-box glass-panel">
            <div class="login-header">
                <div class="logo-wrapper">
                    <img src="/imagenes/escudo.jpeg" alt="Escudo Liceo Samario" class="login-logo">
                </div>
                <h1>I.E.D. LICEO SAMARIO</h1>
                <p>Registro de Nuevo Usuario</p>
            </div>
            
            <form @submit.prevent="submit" class="login-form">
                
                <!-- Campo Nombre -->
                <div class="input-group">
                    <label for="name">Nombre Completo</label>
                    <input 
                        id="name"
                        type="text" 
                        placeholder="Ej. Juan Pérez" 
                        v-model="form.name" 
                        required 
                        autofocus
                        autocomplete="name"
                        class="modern-input"
                    >
                    <div v-if="form.errors.name" class="error-text">
                        {{ form.errors.name }}
                    </div>
                </div>

                <!-- Campo Correo Electrónico -->
                <div class="input-group">
                    <label for="email">Correo Electrónico</label>
                    <input 
                        id="email"
                        type="email" 
                        placeholder="correo@ejemplo.com" 
                        v-model="form.email" 
                        required 
                        autocomplete="username"
                        class="modern-input"
                    >
                    <div v-if="form.errors.email" class="error-text">
                        {{ form.errors.email }}
                    </div>
                </div>
                
                <!-- Campo Contraseña -->
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input 
                        id="password"
                        type="password" 
                        placeholder="••••••••" 
                        v-model="form.password" 
                        required
                        autocomplete="new-password"
                        class="modern-input"
                    >
                    <div v-if="form.errors.password" class="error-text">
                        {{ form.errors.password }}
                    </div>
                </div>

                <!-- Campo Confirmar Contraseña -->
                <div class="input-group">
                    <label for="password_confirmation">Confirmar Contraseña</label>
                    <input 
                        id="password_confirmation"
                        type="password" 
                        placeholder="••••••••" 
                        v-model="form.password_confirmation" 
                        required
                        autocomplete="new-password"
                        class="modern-input"
                    >
                    <div v-if="form.errors.password_confirmation" class="error-text">
                        {{ form.errors.password_confirmation }}
                    </div>
                </div>
                
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    <span v-if="form.processing">Procesando...</span>
                    <span v-else>Crear Cuenta</span>
                </button>

                <!-- Enlace hacia la vista de login -->
                <div class="register-section">
                    <p>¿Ya tienes una cuenta? 
                        <Link :href="route('login')" class="register-link">Inicia sesión aquí</Link>
                    </p>
                </div>
                
            </form>
        </div>
    </section>
</template>

<style scoped>
/* Tipografía base limpia */
* {
    box-sizing: border-box;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

/* Fondo principal con padding para que no se pegue en pantallas pequeñas si el form es muy largo */
.auth-container {
    min-height: 100vh;
    width: 100vw;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #3b82f6 100%);
    position: relative;
    overflow: hidden;
    margin: 0;
    padding: 20px; 
}

/* Figuras flotantes de fondo para darle el toque moderno */
.shape {
    position: absolute;
    filter: blur(60px);
    z-index: 1;
    border-radius: 50%;
}

.shape-1 {
    width: 400px;
    height: 400px;
    background: rgba(59, 130, 246, 0.6);
    top: -100px;
    left: -100px;
}

.shape-2 {
    width: 300px;
    height: 300px;
    background: rgba(139, 92, 246, 0.4);
    bottom: -50px;
    right: -50px;
}

/* Tarjeta principal con efecto Glassmorphism */
.glass-panel {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
    border-radius: 24px;
    padding: 40px;
    width: 100%;
    max-width: 420px;
    z-index: 2;
    color: white;
}

/* Cabecera y Logo */
.login-header {
    text-align: center;
    margin-bottom: 25px;
}

.logo-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: white;
    padding: 4px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.login-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 50%;
}

.login-header h1 {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 5px;
    letter-spacing: 0.5px;
}

.login-header p {
    font-size: 14px;
    color: #cbd5e1;
    margin: 0;
    font-weight: 300;
}

/* Inputs de formulario - Soft UI */
.input-group {
    margin-bottom: 16px;
    text-align: left;
}

.input-group label {
    display: block;
    font-size: 13px;
    margin-bottom: 8px;
    color: #e2e8f0;
    font-weight: 500;
}

.modern-input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    color: white;
    font-size: 14px;
    transition: all 0.3s ease;
    outline: none;
}

.modern-input::placeholder {
    color: rgba(255, 255, 255, 0.4);
}

.modern-input:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
}

.error-text {
    color: #fca5a5;
    font-size: 12px;
    margin-top: 6px;
}

/* Botón principal */
.btn-primary {
    width: 100%;
    padding: 14px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    margin-top: 10px;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
}

.btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Enlace a Login */
.register-section {
    margin-top: 25px;
    text-align: center;
    font-size: 14px;
    color: #cbd5e1;
}

.register-link {
    color: #60a5fa;
    text-decoration: none;
    font-weight: 600;
    margin-left: 5px;
    transition: color 0.2s ease;
}

.register-link:hover {
    color: #93c5fd;
    text-decoration: underline;
}

/* Responsive para móviles */
@media (max-width: 480px) {
    .glass-panel {
        padding: 30px 20px;
        margin: 0;
        border-radius: 20px;
    }
    
    .shape-1 {
        width: 300px;
        height: 300px;
    }
}
</style>
