<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['delta', 'reason'];
    protected $casts = ['delta' => 'integer'];

    public function item() { return $this->belongsTo(Item::class); }
}
