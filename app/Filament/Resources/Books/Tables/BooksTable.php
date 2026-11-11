<?php

namespace App\Filament\Resources\Books\Tables;

use App\Models\Book;
use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BooksTable
{
    private const MAX_ACTIVE_LOANS = 3;

    private const LOAN_DAYS = 7;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('author')
                    ->label('Penulis')
                    ->searchable(),

                TextColumn::make('publisher')
                    ->label('Penerbit'),

                TextColumn::make('year')
                    ->label('Tahun'),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('pinjam')
                    ->label('Pinjam')
                    ->icon('heroicon-o-book-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()?->role === 'siswa')
                    ->disabled(fn (Book $record) => $record->stock < 1)
                    ->action(function (Book $record) {
                        $user = Auth::user();

                        try {
                            DB::transaction(function () use ($record, $user) {
                                $book = Book::query()
                                    ->lockForUpdate()
                                    ->findOrFail($record->id);

                                if ($book->stock < 1) {
                                    throw new \RuntimeException('Stok buku sudah habis.');
                                }

                                $activeLoans = Loan::where('user_id', $user->id)
                                    ->where('status', 'dipinjam');

                                if ($activeLoans->count() >= self::MAX_ACTIVE_LOANS) {
                                    throw new \RuntimeException(
                                        'Maksimal peminjaman adalah '.self::MAX_ACTIVE_LOANS.' buku.'
                                    );
                                }

                                if (Loan::where('user_id', $user->id)
                                    ->where('book_id', $book->id)
                                    ->where('status', 'dipinjam')
                                    ->exists()) {
                                    throw new \RuntimeException('Kamu sedang meminjam buku ini.');
                                }

                                Loan::create([
                                    'user_id' => $user->id,
                                    'book_id' => $book->id,
                                    'loan_date' => today(),
                                    'due_date' => today()->addDays(self::LOAN_DAYS),
                                    'status' => 'dipinjam',
                                ]);

                                $book->decrement('stock');
                            });
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Gagal meminjam buku')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Buku berhasil dipinjam')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn () => Auth::user()?->role === 'admin'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role === 'admin'),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn () => Auth::user()?->role === 'admin'
            );
    }
}
