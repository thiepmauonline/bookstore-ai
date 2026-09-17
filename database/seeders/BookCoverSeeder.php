<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookCoverSeeder extends Seeder
{
    public function run(): void
    {
        $covers = json_decode(file_get_contents(database_path('data/book-covers.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach ($covers as $path) {
            if (! is_file(public_path($path))) {
                throw new RuntimeException("Missing book cover: {$path}");
            }
        }

        // Update demo artwork without recreating books, users, or orders.
        DB::transaction(function () use ($covers) {
            foreach ($covers as $title => $path) {
                Book::where('title', $title)->update(['cover_image' => $path]);
            }
        });
    }
}
