<script setup>
import { ref, watch } from 'vue';
import { useForm, router, Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    estudiantes: Object, // Cambiado de Array a Object por la paginación
    cursos: Array,
    stats: Object,
    search_actual: String,
    curso_actual: [String, Number],
});

const mostrarModal = ref(false);
const tituloModal = ref('Registro de Estudiante');
const qrGeneradoSvg = ref(null);

// Filtros Reactivos
const searchFiltro = ref(props.search_actual);
const cursoFiltro = ref(props.curso_actual);
let timeout = null;

// Escuchamos cambios en buscador y select de cursos para pedir datos al backend
watch([searchFiltro, cursoFiltro], ([nuevoSearch, nuevoCurso]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('estudiantes.index'), { search: nuevoSearch, id_curso: nuevoCurso }, {
            preserveState: true,
            replace: true
        });
    }, 400);
});

const form = useForm({
    id_estudiante: null,
    nombre: '',
    apellido: '',
    id_curso: '',
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

const verQR = async (est) => {
    try {
        const response = await axios.get(route('estudiantes.qr', est.id_estudiante));
        form.id_estudiante = est.id_estudiante;
        form.nombre = est.nombre;
        form.apellido = est.apellido;
        qrGeneradoSvg.value = response.data.qr; 
        tituloModal.value = `Carnet de ${est.nombre}`;
        mostrarModal.value = true;
    } catch (error) {
        console.error(error);
    }
};

const guardarEstudiante = () => {
    if (form.id_estudiante) {
        form.put(route('estudiantes.update', form.id_estudiante), {
            onSuccess: () => mostrarModal.value = false
        });
    } else {
        form.post(route('estudiantes.store'), {
            onSuccess: (page) => {
                if (page.props.flash.qrCode) {
                    qrGeneradoSvg.value = page.props.flash.qrCode;
                    tituloModal.value = '¡Estudiante Guardado!';
                }
            }
        });
    }
};

const obtenerIniciales = (nombre, apellido) => {
    return (nombre.charAt(0) + apellido.charAt(0)).toUpperCase();
};
</script>

<template>
    <Head title="Estudiantes - SICEAP" />
    <DashboardLayout>
        
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon blue">👥</div><div class="stat-info"><h3>Total Estudiantes</h3><p class="stat-value">{{ stats.total_estudiantes }}</p></div></div>
            <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-info"><h3>Activos Hoy</h3><p class="stat-value">{{ stats.activos_hoy }}</p></div></div>
            <div class="stat-card"><div class="stat-icon purple">📚</div><div class="stat-info"><h3>Cursos Totales</h3><p class="stat-value">{{ stats.total_cursos }}</p></div></div>
        </div>

        <div class="table-container">
            <div class="table-header-actions">
                <h3>Listado de Alumnos</h3>
                <div class="actions-group" style="display: flex; gap: 10px;">
                    <input type="text" v-model="searchFiltro" placeholder="🔍 Buscar estudiante..." class="form-control" style="width: 200px; border-radius: 8px; border: 1px solid #d1d5db; padding: 5px 10px;">
                    
                    <select v-model="cursoFiltro" class="select-modern">
                        <option value="">Todos los cursos</option>
                        <option v-for="curso in cursos" :key="curso.id_curso" :value="curso.id_curso">{{ curso.nombre }}</option>
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
                    <tr v-if="estudiantes.data.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">No se encontraron estudiantes con esos filtros.</td>
                    </tr>
                    <tr v-for="est in estudiantes.data" :key="est.id_estudiante">
                        <td><strong>#{{ est.id_estudiante }}</strong></td>
                        <td><div class="avatar-td">{{ obtenerIniciales(est.nombre, est.apellido) }}</div></td>
                        <td>{{ est.nombre }} {{ est.apellido }}</td>
                        <td>{{ est?.curso?.nombre || 'Sin curso' }}</td>
                        <td><span class="badge" :class="est.estado ? 'bg-success text-white' : 'bg-danger text-white'">{{ est.estado ? 'Activo' : 'Inactivo' }}</span></td>
                        <td>
                            <button class="btn-action" @click="verQR(est)" title="Ver QR">🔍</button>
                            <button class="btn-action" @click="editarEstudiante(est)" title="Editar">✏️</button>
                            <button class="btn-action text-danger" @click="eliminarEstudiante(est.id_estudiante)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="estudiantes.links.length > 3" style="display: flex; justify-content: center; gap: 5px; margin-top: 20px;">
                <template v-for="(link, idx) in estudiantes.links" :key="idx">
                    <Link v-if="link.url" :href="link.url" style="padding: 6px 12px; border-radius: 5px; text-decoration: none; border: 1px solid #e5e7eb;" :style="link.active ? 'background-color: var(--primary-color); color: white;' : 'background-color: white; color: #374151;'" v-html="link.label"></Link>
                    <span v-else style="padding: 6px 12px; border-radius: 5px; background-color: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb;" v-html="link.label"></span>
                </template>
            </div>
        </div>

        <div v-if="mostrarModal" class="modal" style="display: flex;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>{{ tituloModal }}</h3>
                    <span class="close-modal" @click="mostrarModal = false">&times;</span>
                </div>
                <form @submit.prevent="guardarEstudiante">
                    <div class="form-row">
                        <div class="form-group"><label>Nombre</label><input type="text" v-model="form.nombre" required :disabled="qrGeneradoSvg"></div>
                        <div class="form-group"><label>Apellido</label><input type="text" v-model="form.apellido" required :disabled="qrGeneradoSvg"></div>
                    </div>
                    <div class="form-group mb-3" v-if="!qrGeneradoSvg">
                        <label>Curso</label>
                        <select v-model="form.id_curso" class="select-modern" required>
                            <option value="" disabled>Seleccione un curso...</option>
                            <option v-for="curso in cursos" :key="curso.id_curso" :value="curso.id_curso">{{ curso.nombre }}</option>
                        </select>
                    </div>
                    <div v-if="qrGeneradoSvg" class="qr-container text-center">
                        <p>Código QR del Estudiante:</p>
                        <div id="qr-result" v-html="qrGeneradoSvg" class="mb-3"></div>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" class="btn-secondary" onclick="window.print()">🖨️ Imprimir</button>
                            <a :href="route('estudiantes.qr.descargar', form.id_estudiante)" class="btn btn-primary text-white" style="text-decoration: none;" download>⬇️ Descargar QR</a>
                        </div>
                    </div>
                    <div class="modal-buttons mt-3">
                        <button type="button" class="btn-secondary" @click="mostrarModal = false">Cerrar</button>
                        <button v-if="!qrGeneradoSvg" type="submit" class="btn-primary" :disabled="form.processing">{{ form.id_estudiante ? 'Actualizar' : 'Guardar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>