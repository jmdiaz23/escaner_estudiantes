<?php

namespace App\Http\Controllers;

use App\Models\ScanLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    public function index(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::now('America/Bogota')->toDateString());
        $search = $request->input('search'); 

        // 1. Preparamos la consulta base
        $query = ScanLog::with(['estudiante.curso'])
            ->whereDate('fecha_hora', $fecha)
            ->orderBy('fecha_hora', 'desc');

       
        if ($search) {
            $query->whereHas('estudiante', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('id_estudiante', 'like', "%{$search}%");
            });
        }

       
        $asistencias = $query->paginate(15)->through(function ($log) {
            $log->hora_formateada = Carbon::parse($log->fecha_hora)->format('h:i A');
            return $log;
        })->withQueryString(); 

        return Inertia::render('Asistencias/Index', [
            'asistencias' => $asistencias,
            'fecha_actual' => $fecha,
            'search_actual' => $search ?? '',
        ]);
    }
}