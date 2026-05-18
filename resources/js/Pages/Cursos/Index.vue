<script setup>
import { ref } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    cursos: Array,
});

const mostrarModal = ref(false);
const tituloModal = ref('Nuevo Curso');

const form = useForm({
    id_curso: null,
    nombre: '',
});

const mostrarModalLista = ref(false);
const cursoSeleccionadoNombre = ref('');
const listaEstudiantes = ref([]);
const cargandoEstudiantes = ref(false);

const abrirModalNuevo = () => {
    form.reset();
    form.clearErrors();
    form.id_curso = null;
    tituloModal.value = 'Nuevo Curso';
    mostrarModal.value = true;
};

const editarCurso = (curso) => {
    form.clearErrors();
    form.id_curso = curso.id_curso;
    form.nombre = curso.nombre;
    tituloModal.value = 'Editar Curso';
    mostrarModal.value = true;
};

const eliminarCurso = (id) => {
    if (confirm('¿Estás seguro de eliminar este curso? Se eliminarán todos los estudiantes asociados a él.')) {
        router.delete(route('cursos.destroy', id));
    }
};

const guardarCurso = () => {
    if (form.id_curso) {
        form.put(route('cursos.update', form.id_curso), {
            onSuccess: () => mostrarModal.value = false
        });
    } else {
        form.post(route('cursos.store'), {
            onSuccess: () => mostrarModal.value = false
        });
    }
};
const verEstudiantes = async (curso) => {
    cursoSeleccionadoNombre.value = curso.nombre;
    listaEstudiantes.value = []; 
    cargandoEstudiantes.value = true;
    mostrarModalLista.value = true;

    try {
        const response = await axios.get(route('cursos.estudiantes', curso.id_curso));
        listaEstudiantes.value = response.data;
    } catch (error) {
        console.error("Error cargando la lista de estudiantes:", error);
    } finally {
        cargandoEstudiantes.value = false;
    }
};
</script>

<template>
    <Head title="Cursos" />
    <DashboardLayout>
        
      
        <div v-if="$page.props.flash.mensaje" class="alert alert-success shadow-sm mb-4">
            {{ $page.props.flash.mensaje }}
        </div>

        <div class="table-container">
            <div class="table-header-actions">
                <h3>Gestión de Cursos / Salones</h3>
                <button class="btn-add" @click="abrirModalNuevo">+ Nuevo Curso</button>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th>Nombre del Curso</th>
                        <th width="15%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                 
                    <tr v-if="cursos.length === 0">
                        <td colspan="3" class="text-center text-muted py-4">No hay cursos registrados en el sistema.</td>
                    </tr>
                    
                    <tr v-for="curso in cursos" :key="curso.id_curso">
                        <td><strong>#{{ curso.id_curso }}</strong></td>
                        <td>{{ curso.nombre }}</td>
                        <td class="text-center">
                            <button class="btn-action text-info" @click="verEstudiantes(curso)" title="Ver Estudiantes matriculados">👥</button>
                            <button class="btn-action" @click="editarCurso(curso)" title="Editar">✏️</button>
                            <button class="btn-action text-danger" @click="eliminarCurso(curso.id_curso)" title="Eliminar">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL -->
        <div v-if="mostrarModal" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header">
                    <h3>{{ tituloModal }}</h3>
                    <span class="close-modal" @click="mostrarModal = false">&times;</span>
                </div>
                
                <form @submit.prevent="guardarCurso">
                    <div class="form-group mb-3">
                        <label>Nombre del Curso (Ej: 11-1)</label>
                        <input type="text" v-model="form.nombre" class="form-control" placeholder="Escribe el nombre..." required>
                      
                        <div v-if="form.errors.nombre" class="text-danger mt-1" style="font-size: 12px;">
                            {{ form.errors.nombre }}
                        </div>
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="btn-secondary" @click="mostrarModal = false">Cancelar</button>
                        <button type="submit" class="btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : (form.id_curso ? 'Actualizar' : 'Guardar') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="mostrarModalLista" class="modal" style="display: flex;">
            <div class="modal-content" style="max-width: 500px;">
                <div class="modal-header">
                    <h3>Estudiantes en {{ cursoSeleccionadoNombre }}</h3>
                    <span class="close-modal" @click="mostrarModalLista = false">&times;</span>
                </div>
                
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div v-if="cargandoEstudiantes" class="text-center py-3 text-muted">
                        Cargando lista...
                    </div>
                    
                    <div v-else-if="listaEstudiantes.length === 0" class="text-center py-4 text-muted">
                        📚 Aún no hay estudiantes matriculados en este curso.
                    </div>
                    
                    <ul v-else class="list-group" style="list-style: none; padding: 0; margin: 0;">
                        <li v-for="estudiante in listaEstudiantes" :key="estudiante.id_estudiante" 
                            style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>{{ estudiante.nombre }} {{ estudiante.apellido }}</strong>
                            </div>
                            <span class="badge" :class="estudiante.estado ? 'bg-success' : 'bg-danger'" style="color: white; font-size: 11px;">
                                {{ estudiante.estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="modal-buttons mt-3">
                    <button type="button" class="btn-primary btn-block" @click="mostrarModalLista = false">Cerrar Lista</button>
                </div>
            </div>
        </div>


    </DashboardLayout>
</template>