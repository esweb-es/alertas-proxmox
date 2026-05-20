<?php

namespace App\Models;

use CodeIgniter\Model;

class PingLogModel extends Model
{
    protected $table            = 'ping_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'empresa_id',
        'status',
        'latency',
        'created_at'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates - we set created_at manually in cron and view
    protected $useTimestamps = false;
}
