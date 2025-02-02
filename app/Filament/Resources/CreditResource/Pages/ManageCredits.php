<?php
namespace App\Filament\Resources\CreditResource\Pages;

use App\Filament\Resources\CreditResource;
use App\Models\Credit;
use App\Models\Sale;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;

class ManageCredits extends ManageRecords
{
    protected static string $resource = CreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Pay Credit')
                ->slideOver()
                ->modalWidth(MaxWidth::Medium)
                ->mutateFormDataUsing(function (array $data): array {
                    $credit = Credit::query()->latest()->find($data['credit_id']);
                    $data['order_number'] = $credit->order_number;
                    $data['amount_owed'] = floatval($data['balance']) - floatval($data['amount_paid']);
                    $data['status'] = match (true) {
                        $data['balance'] == 0 => 'paid',
                        $data['amount_owed'] == $credit->balance => 'unpaid',
                        $data['amount_owed'] < $credit->balance => 'partially_paid',
                        default => 'unpaid',
                    };
                    $data['balance'] = $data['amount_owed'];
                    return $data;
                })
                ->after(function (array $data) {
                    // Retrieve the last entered credit record with the same order_number
                    $latestCredit = Credit::where('order_number', $data['order_number'])
                        ->latest() // Get the most recent record
                        ->first(); // Get only the first (latest) record

                    // If the credit record exists and the difference between amount_paid and amount_owed is 0
                    if ($latestCredit && floatval($data['amount_owed']) == 0) {
                        // Update the Sale record where the order_number matches
                        Sale::where('order_number', $data['order_number'])
                            ->update(['payment_status' => 'paid']);

                        // Update the latest Credit record's status to 'paid'
                        $latestCredit->update(['status' => 'paid']);
                    }
                })
        ];
    }
}
