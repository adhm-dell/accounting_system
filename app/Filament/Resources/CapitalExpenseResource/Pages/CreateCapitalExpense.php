<?php

namespace App\Filament\Resources\CapitalExpenseResource\Pages;

use App\Filament\Resources\CapitalExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCapitalExpense extends CreateRecord
{
    protected static string $resource = CapitalExpenseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
