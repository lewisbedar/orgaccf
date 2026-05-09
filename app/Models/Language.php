<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'level', 'icon_path', 'sort_order', 'is_active'])]
class Language extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function label(): string
    {
        return "{$this->name} {$this->level}";
    }
}
