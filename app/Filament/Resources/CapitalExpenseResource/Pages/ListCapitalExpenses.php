<?php

namespace App\Filament\Resources\CapitalExpenseResource\Pages;

use App\Filament\Resources\CapitalExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCapitalExpenses extends ListRecords
{
    protected static string $resource = CapitalExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('إضافة مصروف'),
        ];
    }
}
