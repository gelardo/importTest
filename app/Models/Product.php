<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'tblProductData';
    public $timestamps = false;
    protected $fillable = [
        'strProductName',
        'strProductDesc',
        'strProductCode',
        'stock_level',
        'price',
        'dtmAdded',
        'dtmDiscontinued',
    ];
}

