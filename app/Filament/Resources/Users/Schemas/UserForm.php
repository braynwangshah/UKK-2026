<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Lengkap')
                ->required(),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true),

            Select::make('role')
                ->label('Role')
                ->options([
                    'admin' => 'Admin',
                    'siswa' => 'Siswa',
                ])
                ->required()
                ->default('siswa'),

            TextInput::make('kelas')
                ->label('Kelas')
                ->visible(fn ($get) => $get('role') === 'siswa'),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create'
                ),
        ]);
    }
}
