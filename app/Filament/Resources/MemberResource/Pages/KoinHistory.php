<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Koinhistory as ModelsKoinhistory;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;

class KoinHistory extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static string $resource = MemberResource::class;

    protected static string $view = 'filament.resources.member-resource.pages.koin-history';

    

    protected function getTableQuery()
    {
        $user = auth()->user();
    
        if ($user && $user->group_id > 0) {

            $dataList = ModelsKoinhistory::query()->where('group_id',$user->group_id)->orderBy('created_at', 'desc');

        }else{
            $dataList = ModelsKoinhistory::query()->orderBy('created_at', 'desc');
        }

        return $dataList;
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
