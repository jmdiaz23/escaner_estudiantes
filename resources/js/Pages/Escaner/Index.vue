<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Html5Qrcode } from 'html5-qrcode';
import axios from 'axios';

const mensaje = ref('');
const tipoAlerta = ref(''); 
const procesando = ref(false);
let html5QrCode = null;

// Función que se ejecuta cuando la cámara detecta un QR
const onScanSuccess = async (decodedText) => {
    // Si ya estamos procesando un código, ignoramos los demás para no saturar el servidor
    if (procesando.value) return;
    procesando.value = true;

    try {
        // Convertimos el texto del QR (que guardamos como JSON en Laravel) a un objeto
        const datosQr = JSON.parse(decodedText);
        
        if (!datosQr.id) throw new Error("QR no pertenece al sistema.");

        // Hacemos la petición silenciosa a nuestro ScanController
        const response = await axios.post(route('escaner.registrar'), {
            id_estudiante: datosQr.id
        });

        mensaje.value = response.data.mensaje;
        tipoAlerta.value = 'success';

        // Opcional: Reproducir un sonido de "Beep" de éxito aquí

    } catch (error) {
        console.error(error);
        tipoAlerta.value = 'error';
        if (error.response && error.response.data) {
            mensaje.value = error.response.data.mensaje; // Mensaje del backend (ej. inactivo)
        } else {
            mensaje.value = 'Error al leer el QR. Asegúrate de que sea un carnet válido de SICEAP.';
        }
    }

    // Esperamos 3 segundos antes de permitir que se escanee el siguiente carnet
    setTimeout(() => {
        procesando.value = false;
        mensaje.value = '';
    }, 3000);
};

onMounted(() => {
    // Inicializamos la cámara en el div con id "reader"
    html5QrCode = new Html5Qrcode("reader");
    
    // Configuramos el tamaño del cuadro de lectura
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    // Encendemos la cámara (pedirá permisos al navegador)
    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
        .catch((err) => {
            console.error("Error al iniciar cámara:", err);
            mensaje.value = "No se pudo acceder a la cámara. Verifica los permisos de tu navegador.";
            tipoAlerta.value = 'error';
        });
});

// Apagamos la cámara si el usuario cambia de página (para que no quede el foquito encendido)
onBeforeUnmount(() => {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().catch(err => console.error(err));
    }
});
</script>

<template>
    <Head title="Escáner - LICEO-SAMARIO" />
    <DashboardLayout>
        
        <div class="container mt-4">
            <div class="row justify-content-center text-center">
                <div class="col-md-8">
                    <h2 class="mb-3" style="font-weight: 600; color: var(--primary-color);">Punto de Control SICEAP</h2>
                    <p class="text-muted mb-4">Posiciona el código QR del carnet estudiantil frente a la cámara.</p>

                    <!-- Mensajes de Alerta -->
                    <div v-if="mensaje" 
                         class="alert shadow-sm" 
                         :class="tipoAlerta === 'success' ? 'alert-success' : 'alert-danger'" 
                         role="alert"
                         style="font-size: 1.1rem; font-weight: 500;">
                        {{ mensaje }}
                    </div>

                    <!-- Contenedor de la Cámara -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-1" style="background-color: #000; border-radius: 8px; overflow: hidden;">
                            <div id="reader" width="100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </DashboardLayout>
</template>

<style scoped>
/* Aseguramos que el escáner se vea bien en pantallas grandes */
#reader {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}
</style>