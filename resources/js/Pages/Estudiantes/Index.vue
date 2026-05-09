<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    estudiantes: Array,
    cursos: Array,
    stats: Object,
});

const mostrarModal = ref(false);
const tituloModal = ref('Registro de Estudiante');
const qrGeneradoSvg = ref(null);
const filtroCurso = ref(''); 

const form = useForm({
    id_estudiante: null,
    nombre: '',
    apellido: '',
    id_curso: '',
});

// Filtramos la tabla dinámicamente sin recargar la página
const estudiantesFiltrados = computed(() => {
    if (!filtroCurso.value) return props.estudiantes;
    return props.estudiantes.filter(est => est.id_curso === filtroCurso.value);
});

const abrirModalNuevo = () => {
    form.reset();
    form.id_estudiante = null;
    qrGeneradoSvg.value = null; 
    tituloModal.value = 'Registro de Estudiante';
    mostrarModal.value = true;
};

const editarEstudiante = (est) => {
    form.id_estudiante = est.id_estudiante;
    form.nombre = est.nombre;
    form.apellido = est.apellido;
    form.id_curso = est.id_curso;
    qrGeneradoSvg.value = null;
    tituloModal.value = 'Editar Estudiante';
    mostrarModal.value = true;
};

const eliminarEstudiante = (id) => {
    if (confirm('¿Estás seguro de eliminar a este estudiante?')) {
        router.delete(route('estudiantes.destroy', id));
    }
};

const verQR = (est) => {
   
    alert(`Aquí se mostrará de nuevo el QR de ${est.nombre}`);
};

const guardarEstudiante = () => {
    if (form.id_estudiante) {
        // MODO EDICIÓN
        form.put(route('estudiantes.update', form.id_estudiante), {
            onSuccess: () => mostrarModal.value = false
        });
    } else {
        // MODO CREACIÓN
        form.post(route('estudiantes.store'), {
            onSuccess: (page) => {
                if (page.props.flash.qrCode) {
                    qrGeneradoSvg.value = page.props.flash.qrCode;
                    tituloModal.value = '¡Estudiante Guardado con Éxito!';
                }
            }
        });
    }
};

// Función para obtener las iniciales del estudiante
const obtenerIniciales = (nombre, apellido) => {
    return (nombre.charAt(0) + apellido.charAt(0)).toUpperCase();
};
</script>

<template>
    <DashboardLayout>
        
      
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">👥</div>
                <div class="stat-info">
                    <h3>Total Estudiantes</h3>
                    <p class="stat-value">{{ stats.total_estudiantes }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-info">
                    <h3>Activos Hoy</h3>
                    <p class="stat-value">{{ stats.activos_hoy }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">📚</div>
                <div class="stat-info">
                    <h3>Cursos Totales</h3>
                    <p class="stat-value">{{ stats.total_cursos }}</p>
                </div>
            </div>
        </div>

        <!-- TABLA -->
        <div class="table-container">
            <div class="table-header-actions">
                <h3>Listado Reciente</h3>
                <div class="actions-group">
                    <select v-model="filtroCurso" class="select-modern">
                        <option value="">Todos los cursos</option>
                        <option v-for="curso in cursos" :key="curso.id_curso" :value="curso.id_curso">
                            {{ curso.nombre }}
                        </option>
                    </select>
                    <button class="btn-add" @click="abrirModalNuevo">+ Nuevo Estudiante</button>
                </div>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nombre Completo</th>
                        <th>Curso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="est in estudiantesFiltrados" :key="est.id_estudiante">
                        <td><strong>#{{ est.id_estudiante }}</strong></td>
                        <td>
                            <!-- Iniciales circulares -->
                            <div class="avatar-td">{{ obtenerIniciales(est.nombre, est.apellido) }}</div>
                        </td>
                        <td>{{ est.nombre }} {{ est.apellido }}</td>
                        <td>{{ est?.curso?.nombre || 'Sin curso' }}</td>
                        <td>
                            <span class="badge" :class="est.estado ? 'bg-success text-white' : 'bg-danger text-white'">
                                {{ est.estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <button class="btn-action" @click="verQR(est)" title="Ver QR">🔍</button>
                            <button class="btn-action" @click="editarEstudiante(est)" title="Editar">✏️</button>
                            <button class="btn-action text-danger" @click="eliminarEstudiante(est.id_estudiante)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL -->
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

                    <!-- Nuevo: Selector de Curso -->
                    <div class="form-group" v-if="!qrGeneradoSvg">
                        <label>Curso</label>
                        <select v-model="form.id_curso" class="select-modern" required>
                            <option value="" disabled>Seleccione un curso...</option>
                            <option v-for="curso in cursos" :key="curso.id_curso" :value="curso.id_curso">
                                {{ curso.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- El QR devuelto por Laravel -->
                    <div v-if="qrGeneradoSvg" class="qr-container">
                        <p>Código QR del Estudiante:</p>
                        <div id="qr-result" v-html="qrGeneradoSvg"></div>
                        <button type="button" class="btn-secondary" style="margin-top:10px; font-size: 12px;" onclick="window.print()">Imprimir Carnet</button>
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="btn-secondary" @click="mostrarModal = false">Cerrar</button>
                        <button v-if="!qrGeneradoSvg" type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.id_estudiante ? 'Actualizar Estudiante' : 'Guardar Estudiante' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </DashboardLayout>
</template>