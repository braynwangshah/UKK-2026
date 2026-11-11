<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Judul Buku',
                Book::count()
            )
                ->description('Total koleksi buku')
                ->descriptionIcon('heroicon-o-book-open')
                ->color('primary'),

            Stat::make(
                'Anggota',
                User::where('role', 'siswa')->count()
            )
                ->description('Total anggota siswa')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),

            Stat::make(
                'Sedang Dipinjam',
                Loan::where('status', 'dipinjam')->count()
            )
                ->description('Buku yang belum dikembalikan')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
}
