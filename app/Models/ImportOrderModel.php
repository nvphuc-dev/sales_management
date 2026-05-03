<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ImportOrderModel extends Model
{
    protected $table            = 'import_orders';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'code',
        'supplier_id',
        'total_amount',
        'notes',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
