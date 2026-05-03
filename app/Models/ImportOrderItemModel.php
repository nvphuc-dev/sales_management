<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ImportOrderItemModel extends Model
{
    protected $table            = 'import_order_items';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'import_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'line_total',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
