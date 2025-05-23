<?php

namespace App\Filament\Resources\PendingwdResource\Pages;

use App\Filament\Resources\PendingwdResource;
use App\Filament\Resources\PendingwdResource\Widgets\PendingWdOverview;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Pending;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingwds extends ListRecords
{
    protected static string $resource = PendingwdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New Pending WD'),

            Action::make('cuci')
                ->label('Cuci')
                ->color('danger')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Dari Tanggal')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Sampai Tanggal')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $records = Pending::where('type',1)
                        ->whereDate('created_at', '>=', $data['start_date'])
                        ->whereDate('created_at', '<=', $data['end_date'])
                        ->delete();

                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RealtimeClock::class,
            PendingWdOverview::class,
        ];
    }

    
}
