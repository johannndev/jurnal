<?php

namespace App\Filament\Resources\PendingDepoResource\Pages;

use App\Filament\Resources\PendingDepoResource;
use App\Filament\Resources\PendingDepoResource\Widgets\PendingDepoOverview;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Pendingdepo;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendingDepos extends ListRecords
{
    protected static string $resource = PendingDepoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New Pending Depo'),

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
                    $records = Pendingdepo::where('type',1)
                        ->whereDate('created_at', '>=', $data['start_date'])
                        ->whereDate('created_at', '<=', $data['end_date'])
                        ->delete();

                })
                ->visible(fn () =>
                    auth()->user()->hasPermissionTo('create_pendingdepo')
                ),
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
            PendingDepoOverview::class,
        ];
    }

}
