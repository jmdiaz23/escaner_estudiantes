<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    estudiantes: Array,
});

const mostrarModal = ref(false);
const tituloModal = ref('Registro de Estudiante');
const qrGeneradoSvg = ref(null);

const form = useForm({
    nombre: '',
    apellido: '',
});

const abrirModalNuevo = () => {
    form.reset();
    qrGeneradoSvg.value = null; 
    tituloModal.value = 'Registro de Estudiante';
    mostrarModal.value = true;
};

const guardarEstudiante = () => {
    form.post(route('estudiantes.store'), {
        onSuccess: (page) => {
            if (page.props.flash.qrCode) {
                qrGeneradoSvg.value = page.props.flash.qrCode;
                tituloModal.value = '¡Estudiante Guardado con Éxito!';
            }
        }
    });
};
</script>

<template>
    <DashboardLayout>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-info">
                    <h3>Total Estudiantes</h3>
                    <p class="stat-value">{{ estudiantes.length }}</p>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header-actions">
                <h3>Listado Reciente</h3>
                <div class="actions-group">
                    <button class="btn-add" @click="abrirModalNuevo">+ Nuevo Estudiante</button>
                </div>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="est in estudiantes" :key="est.id_estudiante">
                        <td><strong>#{{ est.id_estudiante }}</strong></td>
                        <td>{{ est.nombre }} {{ est.apellido }}</td>
                        <td>
                            <span class="badge">Activo</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL DE REGISTRO -->
        <div v-if="mostrarModal" class="modal" style="display: flex;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ tituloModal }}</h3>
                    <span class="close-modal" @click="mostrarModal = false">&times;</span>
                </div>
                
                <form @submit.prevent="guardarEstudiante">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" v-model="form.nombre" required :disabled="qrGeneradoSvg">
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" v-model="form.apellido" required :disabled="qrGeneradoSvg">
                        </div>
                    </div>

                    <!-- El QR devuelto por Laravel -->
                    <div v-if="qrGeneradoSvg" class="qr-container">
                        <p>Código QR del Estudiante:</p>
                        <div id="qr-result" v-html="qrGeneradoSvg"></div>
                        <button type="button" class="btn-secondary" style="margin-top:10px; font-size: 12px;" onclick="window.print()">Imprimir Carnet</button>
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="btn-secondary" @click="mostrarModal = false">Cerrar</button>
                        <button v-if="!qrGeneradoSvg" type="submit" class="btn-primary" :disabled="form.processing">Guardar Estudiante</button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>