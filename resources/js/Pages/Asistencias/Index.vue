<script setup>
import { ref, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    asistencias: Object, 
    fecha_actual: String,
    search_actual: String,
});

const fechaFiltro = ref(props.fecha_actual);
const searchFiltro = ref(props.search_actual);
let timeout = null;


watch([fechaFiltro, searchFiltro], ([nuevaFecha, nuevoSearch]) => {
   
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('asistencias.index'), { fecha: nuevaFecha, search: nuevoSearch }, {
            preserveState: true,
            replace: true
        });
    }, 400); // Espera 400ms después de que dejes de escribir
});

const imprimirReporte = () => {
    window.print();
};
</script>

<template>
    <Head title="Asistencias - LICEO SAMARIO" />
    <DashboardLayout>
        
        <div class="table-container">
            <div class="table-header-actions" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: space-between;">
                <h3>Reporte de Accesos Diarios</h3>
                
                <div class="actions-group" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    
                    <div style="position: relative;">
                        <span style="position: absolute; left: 10px; top: 8px;">🔍</span>
                        <input type="text" v-model="searchFiltro" placeholder="Buscar alumno..." class="form-control" style="padding-left: 35px; border-radius: 8px; border: 1px solid #d1d5db;">
                    </div>

                    <input type="date" v-model="fechaFiltro" class="form-control" style="width: auto; padding: 8px; border-radius: 8px; border: 1px solid #d1d5db;">
                    
                    <button class="btn-primary" @click="imprimirReporte" style="background-color: #10b981; border: none;">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
                <strong>Resumen:</strong> {{ asistencias.total }} registros encontrados en esta fecha.
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th width="15%">Hora</th>
                        <th>Estudiante</th>
                        <th>Curso</th>
                        <th width="15%">Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="asistencias.data.length === 0">
                        <td colspan="4" class="text-center text-muted py-4">
                            📅 No se encontraron registros.
                        </td>
                    </tr>
                    
                    <tr v-for="log in asistencias.data" :key="log.id_scan_logs">
                        <td><strong>{{ log.hora_formateada }}</strong></td>
                        
                        <td v-if="log.estudiante">
                            {{ log.estudiante.nombre }} {{ log.estudiante.apellido }}
                        </td>
                        <td v-else class="text-muted">Estudiante Eliminado</td>
                        
                        <td>
                            {{ log.estudiante && log.estudiante.curso ? log.estudiante.curso.nombre : 'Sin Curso' }}
                        </td>
                        
                        <td>
                            <span class="badge" :class="log.resultado === 'Exitoso' ? 'bg-success text-white' : 'bg-danger text-white'">
                                {{ log.resultado }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="asistencias.links && asistencias.links.length > 3" style="display: flex; justify-content: center; gap: 5px; margin-top: 20px;" class="pagination-no-print">
                <template v-for="(link, index) in asistencias.links" :key="index">
                    <Link 
                        v-if="link.url" 
                        :href="link.url" 
                        style="padding: 8px 12px; border-radius: 5px; text-decoration: none; border: 1px solid #e5e7eb;"
                        :style="link.active ? 'background-color: var(--primary-color); color: white;' : 'background-color: white; color: #374151;'"
                        v-html="link.label"
                    ></Link>
                    <span 
                        v-else 
                        style="padding: 8px 12px; border-radius: 5px; background-color: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb;"
                        v-html="link.label"
                    ></span>
                </template>
            </div>

        </div>
    </DashboardLayout>
</template>

<style scoped>
@media print {
    .sidebar, .actions-group, .pagination-no-print {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    .table-container {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>