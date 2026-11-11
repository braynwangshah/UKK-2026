<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoansTable
{
    private const FINE_PER_DAY = 1000;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Siswa')
                    ->searchable(),

                TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->searchable(),

                TextColumn::make('loan_date')
                    ->label('Tanggal Pinjam')
                    ->date(),

                TextColumn::make('due_date')
                    ->label('Batas Kembali')
                    ->date(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'dipinjam' => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('fine')
                    ->label('Denda')
                    ->money('IDR'),
            ])
            ->recordActions([
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (Loan $record) {
                        $user = Auth::user();

                        return $record->status === 'dipinjam'
                            && ($user?->role === 'admin' || $record->user_id === $user?->id);
                    })
                    ->action(function (Loan $record) {
                        $user = Auth::user();

                        try {
                            DB::transaction(function () use ($record, $user) {
                                $loan = Loan::query()
                                    ->lockForUpdate()
                                    ->findOrFail($record->id);

                                if ($user?->role !== 'admin' && $loan->user_id !== $user?->id) {
                                    throw new \RuntimeException('Kamu tidak berhak mengembalikan buku ini.');
                                }

                                if ($loan->status !== 'dipinjam') {
                                    throw new \RuntimeException('Peminjaman sudah dikembalikan.');
                                }

                                $today = today();

                                $lateDays = $loan->due_date->lt($today)
                                    ? (int) $loan->due_date->diffInDays($today)
                                    : 0;

                                $loan->update([
                                    'returned_at' => $today,
                                    'status' => 'dikembalikan',
                                    'fine' => $lateDays * self::FINE_PER_DAY,
                                ]);

                                $loan->book()->increment('stock');
                            });
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Gagal mengembalikan buku')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Buku berhasil dikembalikan')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
