<script setup>
import { ref, watch } from 'vue';
import { useForm, router, Head, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';

const props = defineProps({
    cursos: Object, // Cambiado a Object
    search_actual: String,
});

const mostrarModal = ref(false);
const tituloModal = ref('Nuevo Curso');
const searchFiltro = ref(props.search_actual);
let timeout = null;

watch(searchFiltro, (nuevoSearch) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('cursos.index'), { search: nuevoSearch }, {
            preserveState: true,
            replace: true
        });
    }, 400);
});

const form = useForm({ id_curso: null, nombre: '' });

const mostrarModalLista = ref(false);
const cursoSeleccionadoNombre = ref('');
const listaEstudiantes = ref([]);
const cargandoEstudiantes = ref(false);

const abrirModalNuevo = () => {
    form.reset(); form.clearErrors(); form.id_curso = null;
    tituloModal.value = 'Nuevo Curso'; mostrarModal.value = true;
};

const editarCurso = (curso) => {
    form.clearErrors(); form.id_curso = curso.id_curso; form.nombre = curso.nombre;
    tituloModal.value = 'Editar Curso'; mostrarModal.value = true;
};

const eliminarCurso = (id) => {
    if (confirm('¿Estás seguro de eliminar este curso? Se borrarán sus estudiantes asignados.')) {
        router.delete(route('cursos.destroy', id));
    }
};

const guardarCurso = () => {
    if (form.id_curso) {
        form.put(route('cursos.update', form.id_curso), { onSuccess: () => mostrarModal.value = false });
    } else {
        form.post(route('cursos.store'), { onSuccess: () => mostrarModal.value = false });
    }
};

const verEstudiantes = async (curso) => {
    cursoSeleccionadoNombre.value = curso.nombre;
    listaEstudiantes.value = []; cargandoEstudiantes.value = true; mostrarModalLista.value = true;
    try {
        const response = await axios.get(route('cursos.estudiantes', curso.id_curso));
        listaEstudiantes.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        cargandoEstudiantes.value = false;
    }
};
</script>

<template>
    <Head title="Cursos - LICEO SAMARIO" />
    <DashboardLayout>
        
        <div v-if="$page.props.flash.mensaje" class="alert alert-success shadow-sm mb-4">
            {{ $page.props.flash.mensaje }}
        </div>

        <div class="table-container">
            <div class="table-header-actions">
                <h3>Gestión de Cursos / Salones</h3>
                <div class="actions-group" style="display: flex; gap: 10px;">
                    <input type="text" v-model="searchFiltro" placeholder="🔍 Buscar salón..." class="form-control" style="width: 200px; border-radius: 8px; border: 1px solid #d1d5db; padding: 5px 10px;">
                    <button class="btn-add" @click="abrirModalNuevo">+ Nuevo Curso</button>
                </div>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th>Nombre del Curso</th>
                        <th width="20%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="cursos.data.length === 0">
                        <td colspan="3" class="text-center text-muted py-4">No se encontraron cursos registrados.</td>
                    </tr>
                    <tr v-for="curso in cursos.data" :key="curso.id_curso">
                        <td><strong>#{{ curso.id_curso }}</strong></td>
                        <td>{{ curso.nombre }}</td>
                        <td class="text-center">
                            <button class="btn-action text-info" @click="verEstudiantes(curso)" title="Ver Estudiantes">👥</button>
                            <button class="btn-action" @click="editarCurso(curso)" title="Editar">✏️</button>
                            <button class="btn-action text-danger" @click="eliminarCurso(curso.id_curso)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="cursos.links.length > 3" style="display: flex; justify-content: center; gap: 5px; margin-top: 20px;">
                <template v-for="(link, idx) in cursos.links" :key="idx">
                    <Link v-if="link.url" :href="link.url" style="padding: 6px 12px; border-radius: 5px; text-decoration: none; border: 1px solid #e5e7eb;" :style="link.active ? 'background-color: var(--primary-color); color: white;' : 'background-color: white; color: #374151;'" v-html="link.label"></Link>
                    <span v-else style="padding: 6px 12px; border-radius: 5px; background-color: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb;" v-html="link.label"></span>
                </template>
            </div>
        </div>

        <div v-if="mostrarModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header"><h3>{{ tituloModal }}</h3><span class="close-modal" @click="mostrarModal = false">&times;</span></div>
                <form @submit.prevent="guardarCurso">
                    <div class="form-group mb-3">
                        <label>Nombre del Curso (Ej: 11-1)</label>
                        <input type="text" v-model="form.nombre" class="form-control" required>
                        <div v-if="form.errors.nombre" class="text-danger mt-1" style="font-size: 12px;">{{ form.errors.nombre }}</div>
                    </div>
                    <div class="modal-buttons">
                        <button type="button" class="btn-secondary" @click="mostrarModal = false">Cancelar</button>
                        <button type="submit" class="btn-primary" :disabled="form.processing">{{ form.id_curso ? 'Actualizar' : 'Guardar' }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="mostrarModalLista" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header"><h3>Estudiantes en {{ cursoSeleccionadoNombre }}</h3><span class="close-modal" @click="mostrarModalLista = false">&times;</span></div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div v-if="cargandoEstudiantes" class="text-center py-3 text-muted">Cargando...</div>
                    <div v-else-if="listaEstudiantes.length === 0" class="text-center py-4 text-muted">📚 No hay estudiantes matriculados.</div>
                    <ul v-else style="list-style: none; padding: 0; margin: 0;">
                        <li v-for="est in listaEstudiantes" :key="est.id_estudiante" style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                            <div><strong>{{ est.nombre }} {{ est.apellido }}</strong></div>
                            <span class="badge" :class="est.estado ? 'bg-success' : 'bg-danger'" style="color: white; font-size: 11px;">{{ est.estado ? 'Activo' : 'Inactivo' }}</span>
                        </li>
                    </ul>
                </div>
                <div class="modal-buttons mt-3"><button type="button" class="btn-primary btn-block" @click="mostrarModalLista = false">Cerrar Lista</button></div>
            </div>
        </div>
    </DashboardLayout>
</template>