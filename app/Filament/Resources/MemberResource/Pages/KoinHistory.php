<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Koinhistory as ModelsKoinhistory;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;

use App\Models\Group;

class KoinHistory extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static string $resource = MemberResource::class;

    protected static string $view = 'filament.resources.member-resource.pages.koin-history';

    

    protected function getTableQuery()
    {
        $user = auth()->user();
        $groupId = request()->get('group_id',1);

        return ModelsKoinhistory::query()
            ->when($user && $user->group_id > 0, fn ($q) => $q->where('group_id', $user->group_id))
            ->when($user && $user->group_id == 0 && $groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($user && $user->group_id == 0 && !$groupId, fn ($q) => $q->where('group_id', 1)) // default untuk admin
            ->orderBy('created_at', 'desc');
        }

    protected function getTableFilters(): array
    {
        
        $gd = Group::where('is_default', 1)->first();
        $dft = $gd ? $gd->id : 1;

        return [
            Filter::make('by_group')
                ->form([
                    Select::make('group_id')
                        ->label('Group')
                        ->options(Group::pluck('name', 'id')->toArray())
                        ->default(request()->get('group_id', $dft))
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            $url = route('filament.admin.resources.members.koin-history');
                            return redirect($url . ($state ? '?group_id=' . $state : ''));
                        }),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if ($data['group_id'] ?? false) {
                        $group = Group::find($data['group_id']);
                        return 'Group: ' . ($group?->name ?? 'Unknown');
                    }
                    return null;
                })
                ->visible(fn () => auth()->user()->group_id == 0),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            
            TextColumn::make('created_at')->label('Tanggal')->searchable(),
            TextColumn::make('keterangan')->label('keterangan'),
            TextColumn::make('member.username')->label('username'),
            TextColumn::make('koin')->label('koin')->numeric(locale: 'id'),
            TextColumn::make('saldo')->label('saldo')->numeric(locale: 'id'),
            TextColumn::make('operator.name')->label('operator'),
       
            
        ];
    }

}
