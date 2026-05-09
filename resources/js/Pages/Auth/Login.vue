<script setup>
import { Head, useForm } from '@inertiajs/vue3';

// Usamos el formulario de Inertia para mantener la seguridad de Laravel
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <!-- Head nos permite cambiar el título de la pestaña del navegador -->
    <Head title="Iniciar Sesión - LICEO SAMARIO" />

    <section class="auth-container">
        <div class="login-box">
            <div class="login-header">
                <!-- Aquí la ruta ya debe funcionar si pusiste la imagen en la carpeta public -->
                <img src="/imagenes/escudo.jpeg" alt="Escudo Liceo Samario" class="login-logo">
                <h1>I.E.D. LICEO SAMARIO</h1>
                <p>Sistema de Control y Escaneo</p>
            </div>
            
            <form @submit.prevent="submit">
                
                <div class="input-group">
                    <label>Usuario (Correo Electrónico)</label>
                    <!-- Laravel Breeze usa el correo por defecto para el login -->
                    <input 
                        type="email" 
                        placeholder="admin@liceosamario.com" 
                        v-model="form.email" 
                        required 
                        autofocus
                    >
                    <!-- Mostramos error si se equivocan de correo -->
                    <div v-if="form.errors.email" style="color: #ef4444; font-size: 12px; margin-top: 5px;">
                        {{ form.errors.email }}
                    </div>
                </div>
                
                <div class="input-group">
                    <label>Contraseña</label>
                    <input 
                        type="password" 
                        placeholder="••••••••" 
                        v-model="form.password" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn-primary btn-block" :disabled="form.processing">
                    <span v-if="form.processing">Cargando...</span>
                    <span v-else>Entrar al Sistema</span>
                </button>
                
            </form>
        </div>
    </section>
</template>

<style scoped>

.auth-container {
    height: 100vh;
    width: 100vw;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    margin: 0;
    position: absolute;
    top: 0;
    left: 0;
}
</style>