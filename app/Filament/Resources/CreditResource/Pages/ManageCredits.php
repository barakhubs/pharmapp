<?php

namespace App\Filament\Resources\CreditResource\Pages;

use App\Filament\Resources\CreditResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageCredits extends ManageRecords
{
    protected static string $resource = CreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Pay Credit')->slideOver()->modalWidth(MaxWidth::Medium),
        ];
    }
}
