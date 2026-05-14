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

    </DashboardLayout>
</template>