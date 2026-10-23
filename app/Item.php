<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // Owner and quantity are deliberately excluded from ordinary form updates.
    protected $fillable = ['category_id', 'sku', 'name', 'price_cents'];
    protected $attributes = ['quantity' => 0, 'price_cents' => 0];
    protected $casts = ['quantity' => 'integer', 'price_cents' => 'integer'];

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }
}
