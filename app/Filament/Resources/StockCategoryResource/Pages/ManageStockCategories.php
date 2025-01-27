<?php

namespace App\Filament\Resources\StockCategoryResource\Pages;

use App\Filament\Resources\StockCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageStockCategories extends ManageRecords
{
    protected static string $resource = StockCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Create Stock Category')->slideOver()->modalWidth(MaxWidth::Medium),
        ];
    }
}
