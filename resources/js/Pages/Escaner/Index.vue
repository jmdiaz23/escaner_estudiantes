<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Html5Qrcode } from 'html5-qrcode';
import axios from 'axios';

const mensajeError = ref('');
const procesando = ref(false);
let html5QrCode = null;

// Variables para el Modal de Éxito
const mostrarModalExito = ref(false);
const datosEstudiante = ref(null);

const onScanSuccess = async (decodedText) => {
    if (procesando.value) return;
    procesando.value = true;
    mensajeError.value = '';

    try {
        const datosQr = JSON.parse(decodedText);
        if (!datosQr.id) throw new Error("QR inválido.");

        const response = await axios.post(route('escaner.registrar'), {
            id_estudiante: datosQr.id
        });

        // Si es exitoso, cargamos los datos y abrimos el modal
        datosEstudiante.value = response.data.estudiante;
        mostrarModalExito.value = true;

        // Reproducir un sonido de "Beep" exitoso (Opcional, pero da buen UX)
        try {
            const audio = new Audio('/sonidos/beep.mp3'); // Puedes agregar un mp3 en public/sonidos/
            audio.play();
        } catch (e) {}

        // El modal se cierra automáticamente después de 4 segundos para el siguiente estudiante
        setTimeout(() => {
            cerrarModal();
        }, 6000);

    } catch (error) {
        console.error(error);
        if (error.response && error.response.data) {
            mensajeError.value = error.response.data.mensaje;
        } else {
            mensajeError.value = 'Error de lectura. Intente nuevamente.';
        }
        
        // Si hay error, liberamos la cámara rápido
        setTimeout(() => {
            procesando.value = false;
            mensajeError.value = '';
        }, 3000);
    }
};

const cerrarModal = () => {
    mostrarModalExito.value = false;
    datosEstudiante.value = null;
    // Damos medio segundo extra antes de liberar la cámara para evitar dobles lecturas accidentales
    setTimeout(() => {
        procesando.value = false;
    }, 500);
};

onMounted(() => {
    html5QrCode = new Html5Qrcode("reader");
    
    const config = { 
        fps: 20, 
        qrbox: { width: 280, height: 280 },
        aspectRatio: 1.0
    };

    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
        .catch((err) => {
            console.error("Error:", err);
            mensajeError.value = "No se pudo iniciar la cámara.";
        });
});

onBeforeUnmount(async () => {
    if (html5QrCode) {
        try {
            if (html5QrCode.getState() === 2) {
                await html5QrCode.stop();
            }
            html5QrCode.clear();
        } catch (error) {
            window.location.reload(); 
        }
    }
});
</script>

<template>
    <Head title="Escáner - LICEO SAMARIO" />
    <DashboardLayout>
        <div class="container mt-4">
            <div class="row justify-content-center text-center">
                <div class="col-md-8">
                    <h2 class="mb-3" style="font-weight: 600; color: var(--primary-color);">Punto de Control LICEO SAMARIO</h2>
                    <p class="text-muted mb-4">Posiciona el código QR del carnet estudiantil frente a la cámara.</p>

                    <div v-if="mensajeError" class="alert alert-danger shadow-sm mb-3" role="alert" style="font-size: 1.1rem; font-weight: 500;">
                        ❌ {{ mensajeError }}
                    </div>

                    <div class="card shadow-sm border-0 position-relative">
                        <div v-if="procesando && !mensajeError" class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(255,255,255,0.8); z-index: 10;">
                            <h4 class="text-primary">Procesando...</h4>
                        </div>

                        <div class="card-body p-1" style="background-color: #000; border-radius: 8px; overflow: hidden;">
                            <div id="reader" width="100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="mostrarModalExito" class="modal" style="display: flex; background: rgba(0,0,0,0.6);">
            <div class="modal-content text-center" style="max-width: 450px; border-radius: 20px; border-top: 8px solid #10b981;">
                
                <div style="font-size: 60px; color: #10b981; margin-top: -10px;">
                    ✅
                </div>
                
                <h2 style="color: #111827; margin-bottom: 5px;">¡Asistencia Registrada!</h2>
                <p class="text-muted" style="margin-bottom: 20px;">El acceso ha sido guardado en el sistema.</p>
                
                <div style="background: #f3f4f6; padding: 20px; border-radius: 12px; text-align: left;">
                    <div style="margin-bottom: 10px;">
                        <small class="text-muted d-block">Estudiante</small>
                        <strong style="font-size: 18px; color: #1f2937;">{{ datosEstudiante.nombre_completo }}</strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <small class="text-muted d-block">Curso</small>
                            <strong style="color: #3b82f6;">{{ datosEstudiante.curso }}</strong>
                        </div>
                        <div style="text-align: right;">
                            <small class="text-muted d-block">Hora de Ingreso</small>
                            <strong style="color: #1f2937;">{{ datosEstudiante.hora_ingreso }}</strong>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn-primary w-100" style="padding: 12px; font-size: 16px;" @click="cerrarModal">
                        Siguiente Estudiante (Cerrar)
                    </button>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<style scoped>
#reader {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}
</style>