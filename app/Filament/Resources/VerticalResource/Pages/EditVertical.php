<?php

namespace App\Filament\Resources\VerticalResource\Pages;

use App\Filament\Resources\VerticalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVertical extends EditRecord
{
    protected static string $resource = VerticalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
