<?php

namespace App\Filament\Resources\CapitalExpenseResource\Pages;

use App\Filament\Resources\CapitalExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCapitalExpense extends EditRecord
{
    protected static string $resource = CapitalExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
