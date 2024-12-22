<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlxProduct extends Model
{

    protected $fillable = [
        'sku',
        'url',
        'name',
        'image',
        'description',
        'priceCurrency',
        'price',
        'lastRefreshTime',
    ];

    protected $casts = [
        'sku' => 'integer',
        'price' => 'integer',
        'lastRefreshTime' => 'datetime',
    ];

    public function setPriceCurrencyAttribute($value)
    {
        if (strlen($value) !== 3) {
            throw new \InvalidArgumentException('Price currency must be exactly 3 characters.');
        }

        $this->attributes['priceCurrency'] = strtoupper($value);
    }

    public static function existsBySku($sku)
    {
        return self::where('sku', $sku)->exists();
    }

}
