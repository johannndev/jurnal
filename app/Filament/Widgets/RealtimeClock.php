<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class RealtimeClock extends Widget
{
    protected static string $view = 'filament.widgets.realtime-clock';
     protected static ?int $sort = 1; // Atur urutan tampil jika perlu

    protected int | string | array $columnSpan = 4; // Ini membuat widget memiliki lebar 4 kolom

    public static function canView(): bool
    {
        // Jangan tampil jika sedang di dashboard
        return !request()->routeIs('filament.admin.pages.dashboard');
    }
}
