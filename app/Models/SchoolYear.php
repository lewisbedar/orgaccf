<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label', 'starts_on', 'ends_on', 'is_active', 'closed_at'])]
class SchoolYear extends Model
{
    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'closed_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public static function active(): ?self
    {
        return self::where('is_active', true)->first();
    }
}
