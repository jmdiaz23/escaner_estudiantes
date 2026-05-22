<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{

protected $table = 'scan_logs';
    protected $primaryKey = 'id_scan_logs';
    
  
    public $timestamps = false; 

   
    protected $fillable = [
        'id_estudiante',
        'resultado',
        'mensaje'
    ];

    
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'id_estudiante', 'id_estudiante');
    }
}
