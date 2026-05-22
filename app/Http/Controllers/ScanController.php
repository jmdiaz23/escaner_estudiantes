<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\ScanLog;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function registrar(Request $request)
    {
        // Recibimos el ID que leyó la cámara
        $request->validate([
            'id_estudiante' => 'required|integer'
        ]);

        $estudiante = Estudiante::find($request->id_estudiante);

        // 1. Si el QR es falso o el estudiante no existe
        if (!$estudiante) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Estudiante no encontrado en el sistema.'
            ], 404);
        }

        // 2. Si el estudiante está inactivo (expulsado, retirado, etc.)
        if (!$estudiante->estado) {
            ScanLog::create([
                'id_estudiante' => $estudiante->id_estudiante,
                'resultado' => 'Denegado',
                'mensaje' => 'Intento de acceso de estudiante inactivo.'
            ]);

            return response()->json([
                'status' => 'error',
                'mensaje' => "Acceso denegado: {$estudiante->nombre} {$estudiante->apellido} está inactivo."
            ], 403);
        }

        // 3. Si todo es correcto, registramos la asistencia
        ScanLog::create([
            'id_estudiante' => $estudiante->id_estudiante,
            'resultado' => 'Exitoso',
            'mensaje' => 'Entrada registrada correctamente.'
        ]);

        $estudiante->load('curso');

        return response()->json([
            'status' => 'success',
            'mensaje' => '¡Asistencia Registrada!',
            'estudiante' => [
                'nombre_completo' => $estudiante->nombre . ' ' . $estudiante->apellido,
                'curso' => $estudiante->curso ? $estudiante->curso->nombre : 'Sin curso asignado',
                'hora_ingreso' => now()->format('h:i A'),
                'estado' => 'Activo'
            ]
        ], 200);
    }
}