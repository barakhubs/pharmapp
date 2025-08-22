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
                    // Get all credit IDs from the form
                    $creditIds = json_decode($data['credit_ids'] ?? '[]', true);

                    if (empty($creditIds)) {
                        throw new \Exception('No credits found for this customer');
                    }

                    // Get all credits for this customer with remaining balance
                    $customerCredits = Credit::whereIn('id', $creditIds)
                        ->where('balance', '>', 0)
                        ->orderBy('created_at', 'asc') // Pay oldest first
                        ->get();

                    $totalBalance = $customerCredits->sum('balance');
                    $amountToPay = floatval($data['amount_paid']);
                    $remainingPayment = $amountToPay;

                    // Distribute payment across credits (oldest first)
                    foreach ($customerCredits as $credit) {
                        if ($remainingPayment <= 0) break;

                        $creditBalance = $credit->balance;
                        $paymentForThisCredit = min($creditBalance, $remainingPayment);

                        // Calculate new amounts for this credit
                        $newAmountPaid = $credit->amount_paid + $paymentForThisCredit;
                        $newBalance = $credit->amount_owed - $newAmountPaid;
                        $newStatus = $newBalance <= 0 ? 'paid' : 'partially_paid';

                        // Update this credit
                        $credit->update([
                            'amount_paid' => $newAmountPaid,
                            'balance' => max(0, $newBalance),
                            'status' => $newStatus
                        ]);

                        // Update sale payment status if credit is fully paid
                        if ($newStatus === 'paid') {
                            Sale::where('order_number', $credit->order_number)
                                ->update(['payment_status' => 'paid']);
                        }

                        $remainingPayment -= $paymentForThisCredit;
                    }

                    // Return data for the new payment record
                    $data['order_number'] = 'PAYMENT-' . time();
                    $data['amount_owed'] = $amountToPay;
                    $data['amount_paid'] = $amountToPay;
                    $data['balance'] = 0;
                    $data['status'] = 'paid';

                    return $data;
                })
                ->after(function (array $data) {
                    // Payment processing is now handled in mutateFormDataUsing
                    // This creates a payment record for tracking purposes
                })
        ];
    }
}
