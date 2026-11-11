<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\Book;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Nama Siswa')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            Select::make('book_id')
                ->label('Judul Buku')
                ->options(Book::query()->where('stock', '>', 0)->orderBy('title')->pluck('title', 'id'))
                ->searchable()
                ->required(),

            DatePicker::make('loan_date')
                ->label('Tanggal Pinjam')
                ->required(),

            DatePicker::make('due_date')
                ->label('Batas Pengembalian')
                ->required(),

            Select::make('status')
                ->options([
                    'dipinjam' => 'Dipinjam',
                    'dikembalikan' => 'Dikembalikan',
                ])
                ->required(),
        ]);
    }
}
