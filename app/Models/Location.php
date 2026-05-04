<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['dzongkhag_name', 'slug'];

    public static function dzongkhags(): array
    {
        return [
            'Bumthang',
            'Chhukha',
            'Dagana',
            'Gasa',
            'Haa',
            'Lhuntse',
            'Mongar',
            'Paro',
            'Pema Gatshel',
            'Punakha',
            'Samdrup Jongkhar',
            'Samtse',
            'Sarpang',
            'Thimphu',
            'Trashigang',
            'Trashiyangtse',
            'Trongsa',
            'Tsirang',
            'Wangdue Phodrang',
            'Zhemgang',
        ];
    }

    public static function ensureDefaultDzongkhags(): void
    {
        foreach (self::dzongkhags() as $name) {
            self::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['dzongkhag_name' => $name]
            );
        }
    }

    public static function orderedDzongkhags()
    {
        self::ensureDefaultDzongkhags();

        return self::orderBy('dzongkhag_name')->get();
    }

    public function houses()
    {
        return $this->hasMany(House::class);
    }
}
