<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Filament\Widgets\RealtimeClock;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        if (auth()->check() && auth()->user()->group_id > 0) {
            return [
                RealtimeClock::class,
                MemberResource\Widgets\GroupStat::class,
            ];
        }
    
        return [RealtimeClock::class];
    }

     public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

   
}
