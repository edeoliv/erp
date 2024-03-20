<?php

namespace App\Models;

use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    public static function getForm(): array {
        return [
            TextInput::make('lead')
                ->required()
                ->maxLength(255),
        ];
    }
}
