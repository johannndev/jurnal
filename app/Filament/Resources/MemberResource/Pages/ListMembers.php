<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Group;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
     
        $groupId = request()->get('group_id');

        $gd = Group::where('is_default',1)->first();
        $dft = 1;
        if($gd){
            $dft = $gd->id;
        }

        return [
            RealtimeClock::class,
            MemberResource\Widgets\GroupStat::make([
                'groupId' =>  $groupId,
                'groupDefault' =>  $dft,
            ])
        ];
        
    }

     public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

   
}
