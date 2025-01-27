<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'System Settings';

    public static function canViewAny(): bool
    {
        return auth()->user()->role == "admin";
    }
    public static function canCreate(): bool
    {
        return Auth::user()->role == "admin";
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role == "admin";
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role == "admin";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Select Branch')
                    ->required(),
                Forms\Components\TextInput::make('username')
                    ->label('Username')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Select::make('role')
                    ->label('Select User Role')
                    ->columnSpanFull()
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Branch Manager',
                        'staff' => 'Normal Staff',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->minLength(6)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->same('password')
                    ->label('Confirm Password'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'gray',
                        'manager' => 'warning',
                        'staff' => 'success',
                    })
                    ->searchable(),
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
                Tables\Actions\EditAction::make()->slideOver()->modalWidth(MaxWidth::Medium),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
