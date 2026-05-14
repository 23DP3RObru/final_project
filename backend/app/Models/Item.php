<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'shipping_type',
        'shipping_cost',
        'condition',
        'category',
        'images',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'quantity' => 'integer',
        'images' => 'array',
    ];

    protected $appends = [
        'image_urls',
    ];

    /**
     * Frontend-ready URLs for stored item images.
     */
    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(function (string $path) {
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                return url(Storage::url($path));
            })
            ->values()
            ->all();
    }

    /**
     * Get the user that owns the item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
