<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'pincode',
        'area_name',
        'district',
        'state',
        'country',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Check if delivery is available for a given pincode
     */
    public static function isServiceable(string $pincode): bool
    {
        return static::where('pincode', $pincode)
            ->where('is_active', true)
            ->exists();
    }
}
