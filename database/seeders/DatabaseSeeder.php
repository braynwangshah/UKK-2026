<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@perpus.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'kelas' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa@perpus.test'],
            [
                'name' => 'Dimas',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'kelas' => 'XI RPL 1',
            ]
        );

        $books = [
            [
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang',
                'year' => 2005,
                'stock' => 5,
            ],
            [
                'title' => 'Bumi',
                'author' => 'Tere Liye',
                'publisher' => 'Gramedia',
                'year' => 2014,
                'stock' => 3,
            ],
            [
                'title' => 'Belajar Laravel',
                'author' => 'Budi Raharjo',
                'publisher' => 'Informatika',
                'year' => 2023,
                'stock' => 10,
            ],
        ];

        foreach ($books as $book) {
            Book::updateOrCreate(
                ['title' => $book['title']],
                $book
            );
        }
    }
}
