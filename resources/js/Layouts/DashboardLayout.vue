<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <main class="dashboard-layout">
        <!-- SIDEBAR -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                <img src="/imagenes/escudo.jpeg" alt="Logo" class="sidebar-logo">
                <div>
                    <span class="brand-name">LICEO SAMARIO</span>
                    <span class="brand-tagline">IED</span>
                </div>
            </div>
            <ul class="nav-links">
                <li class="active">
                    <span class="icon">👥</span> Estudiantes
                </li>
            </ul>
            <div class="sidebar-footer">
                <!-- Laravel Inertia Link para cerrar sesión de forma segura -->
                <Link :href="route('logout')" method="post" as="button" class="btn-logout" type="button">
                    <span class="icon">🚪</span> Cerrar Sesión
                </Link>
            </div>
        </nav>

        <!-- CONTENIDO PRINCIPAL -->
        <section class="main-content">
            <header class="main-header">
                <div class="header-title">
                    <p>Bienvenido de nuevo,</p>
                    <h2>Gestión de Estudiantes</h2>
                </div>
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Buscar estudiante por nombre o ID...">
                </div>
            </header>

            <!-- Aquí se inyecta la vista de Estudiantes -->
            <slot />
            
        </section>
    </main>
</template>