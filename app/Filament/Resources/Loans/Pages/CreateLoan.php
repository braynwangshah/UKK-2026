<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Filament\Resources\Loans\LoanResource;
use App\Models\Book;
use App\Models\Loan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected function getRedirectUrl(): string
    {
        return LoanResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $book = Book::query()->lockForUpdate()->findOrFail($data['book_id']);

            if ($book->stock < 1) {
                throw ValidationException::withMessages([
                    'data.book_id' => 'Stok buku sudah habis.',
                ]);
            }

            $loan = Loan::create($data);
            $book->decrement('stock');

            return $loan;
        });
    }
}
