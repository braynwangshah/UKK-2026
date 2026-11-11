<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Judul Buku')
                ->required()
                ->maxLength(255),

            TextInput::make('author')
                ->label('Penulis')
                ->required(),

            TextInput::make('publisher')
                ->label('Penerbit')
                ->required(),

            TextInput::make('year')
                ->label('Tahun Terbit')
                ->numeric()
                ->required(),

            TextInput::make('stock')
                ->label('Stok')
                ->numeric()
                ->minValue(0)
                ->required(),
        ]);
    }
}
