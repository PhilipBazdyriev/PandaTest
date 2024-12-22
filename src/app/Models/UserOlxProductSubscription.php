<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOlxProductSubscription extends Model
{

    protected $fillable = [
        'user_id',
        'olx_product_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function olxProduct()
    {
        return $this->belongsTo(OlxProduct::class);
    }

}
