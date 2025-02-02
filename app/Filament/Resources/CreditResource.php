<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditResource\Pages;
use App\Filament\Resources\CreditResource\RelationManagers;
use App\Models\Credit;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreditResource extends Resource
{
    protected static ?string $model = Credit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Expenses & Credits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->required()
                    ->label('Select Customer')
                    ->options(
                        Customer::whereHas('credits')
                            ->pluck('name', 'id')
                    )
                    ->preload()
                    ->searchable()
                    ->columnSpanFull()
                    ->native(false)
                    ->reactive()
                    ->afterStateUpdated(
                        function($state, callable $set) {
                            $credit = Credit::where('customer_id', $state)
                                ->latest()
                                ->first();

                            $set('balance', $credit?->balance ?? 0);
                            $set('credit_id', $credit?->id ?? null);
                        }
                    ),

                Forms\Components\TextInput::make('balance')
                    ->readOnly()
                    ->prefix('UGX')
                    ->stripCharacters(',')
                    ->mask(RawJs::make('$money($input)'))
                    ->required()
                    ->columns(1),

                Forms\Components\TextInput::make('amount_paid')
                    ->required()
                    ->label('Amount Paid')
                    ->prefix('UGX')
                    ->stripCharacters(',')
                    ->numeric()
                    ->reactive()
                    ->minValue(0)
                    ->maxValue(fn($get) => $get('balance'))
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        $state > $get('balance') ? $set('amount_paid', $get('balance')) : null
                    ),
                Forms\Components\TextInput::make('credit_id')
                    ->readOnly()
                    ->required()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_owed')
                    ->money('UGX ')
                    ->getStateUsing(fn($record) => $record->amount_owed + $record->amount_paid)
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('UGX ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->money('UGX ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'partially_paid' => 'gray',
                        'paid' => 'success',
                        'pending' => 'warning',
                        'unpaid' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('pay')
                    ->label('Pay')
                    ->icon('heroicon-o-currency-dollar')
                    ->fillForm(fn(Credit $record): array => [
                        'customer_name' => $record->customer->name,
                        'balance' => $record->amount_owed,
                        'order_number' => '#' . $record->order_number,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->readOnly()
                            ->label('Customer Name')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('order_number')
                            ->required()
                            ->readOnly()
                            ->label('Order No.')
                            ->columns(1),
                        Forms\Components\TextInput::make('balance')
                            ->readOnly()
                            ->mask(RawJs::make('$money($input)'))
                            ->prefix('UGX')
                            ->stripCharacters(',')
                            ->required()
                            ->columns(1),
                        Forms\Components\TextInput::make('amount_paid')
                            ->required()
                            ->label('Amount Paid')
                            ->mask(RawJs::make('$money($input)'))
                            ->prefix('UGX')
                            ->stripCharacters(','),
                    ])
                    ->action(
                        function (array $data, $record) {
                            $record->create([
                                'customer_id' => $record->customer_id,
                                'order_number' => $record->order_number,
                                'amount_paid' => $data['amount_paid'],
                                'amount_owed' => floatval($data['balance']) - floatval($data['amount_paid']),
                                'status' => match (true) {
                                    $record->balance == 0 => 'paid',
                                    $record->amount_owed == $record->balance => 'unpaid',
                                    $record->amount_owed > $record->balance => 'partially_paid',
                                    default => 'unpaid'
                                }
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Credit payment added successfully')
                                ->send();
                        }
                    )
                    ->slideOver()
                    ->modalWidth(MaxWidth::Medium),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCredits::route('/'),
        ];
    }
}
