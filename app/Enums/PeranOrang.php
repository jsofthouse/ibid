<?php

namespace App\Enums;

enum PeranOrang: string
{
    case Penulis = 'penulis';
    case Editor = 'editor';
    case Kontributor = 'kontributor';
    case Penerjemah = 'penerjemah';

    public function label(): string
    {
        return match ($this) {
            self::Penulis => 'Penulis',
            self::Editor => 'Editor',
            self::Kontributor => 'Kontributor',
            self::Penerjemah => 'Penerjemah',
        };
    }
}
