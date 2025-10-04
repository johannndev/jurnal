<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function afterCreate(): void
    {
        $user = auth()->user();
    
        if ($user && $user->group_id > 0) {
            $this->record->update([
                'group_id' => $user->group_id
            ]);
        }
    }
    

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
