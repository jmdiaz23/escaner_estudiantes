<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EstudianteController extends Controller
{
    // 1. Mostrar la tabla de estudiantes
    public function index()
    {
        // Traemos todos los estudiantes (si no tienes cursos aún creados, podemos omitir el 'with')
        $estudiantes = Estudiante::with('curso')->orderBy('id_estudiante', 'desc')->get();

        return Inertia::render('Estudiantes/Index', [
            'estudiantes' => $estudiantes,
        ]);
    }

    // 2. Guardar estudiante y generar QR
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
        ]);

        // Guardamos en la BD
        $estudiante = Estudiante::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'id_curso' => 1, // Curso por defecto por ahora para que no falle
            'estado' => 1,
        ]);

        // Generamos la información del QR (El ID es lo vital para escanearlo después)
        $datosQr = json_encode([
            'id' => $estudiante->id_estudiante,
            'nombre' => $estudiante->nombre
        ]);

        // Generamos el QR en formato SVG vectorial (Negro sobre transparente)
        $qrSvg = (string) QrCode::size(200)
            ->color(17, 24, 39) // Color oscuro del SICEAP
            ->generate($datosQr);

        // Redirigimos de vuelta enviando un mensaje de éxito y el QR
        return redirect()->route('estudiantes.index')->with([
            'mensaje' => 'Estudiante registrado correctamente.',
            'qrCode' => $qrSvg
        ]);
    }
}