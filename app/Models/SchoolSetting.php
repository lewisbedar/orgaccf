<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['school_name', 'address', 'phone', 'email', 'logo_path', 'active_school_year_id'])]
class SchoolSetting extends Model
{
    public static function current(): self
    {
        return self::firstOrCreate([], ['school_name' => 'Lycée à configurer']);
    }
}
