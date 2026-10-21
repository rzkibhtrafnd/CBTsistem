<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ==========================================
        // TEST 1: Pemrograman Web Dasar (HTML, CSS, JS)
        // ==========================================
        $test1Id = DB::table('tests')->insertGetId([
            'title' => 'Tes Pemrograman Web Dasar',
            'description' => 'Menguji dasar-dasar HTML5, CSS3, Flexbox/Grid, dan JavaScript ES6+.',
            'duration_minutes' => 30,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionsTest1 = [
            [
                'text' => 'Manakah dari berikut ini yang merupakan atribut HTML5 yang benar untuk membuat input wajib diisi?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'validate="true"', 'correct' => false],
                    ['text' => 'required', 'correct' => true],
                    ['text' => 'important', 'correct' => false],
                    ['text' => 'aria-required="must"', 'correct' => false],
                ]
            ],
            [
                'text' => 'Dalam CSS Flexbox, properti apa yang digunakan untuk mengatur perataan elemen di sepanjang sumbu utama (main axis)?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'align-items', 'correct' => false],
                    ['text' => 'justify-content', 'correct' => true],
                    ['text' => 'align-content', 'correct' => false],
                    ['text' => 'flex-direction', 'correct' => false],
                ]
            ],
            [
                'text' => 'Manakah sintaks JavaScript ES6 yang benar untuk mendeklarasikan fungsi panah (arrow function)?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'const myFunc = function() => {}', 'correct' => false],
                    ['text' => 'function myFunc() => {}', 'correct' => false],
                    ['text' => 'const myFunc = () => {}', 'correct' => true],
                    ['text' => 'var myFunc = ArrowFunction() {}', 'correct' => false],
                ]
            ],
            [
                'text' => 'Apa luaran dari kode JavaScript berikut? `console.log(typeof NaN);`',
                'weight' => 10,
                'choices' => [
                    ['text' => '"number"', 'correct' => true],
                    ['text' => '"NaN"', 'correct' => false],
                    ['text' => '"undefined"', 'correct' => false],
                    ['text' => '"null"', 'correct' => false],
                ]
            ],
            [
                'text' => 'Manakah dari Utility Class Tailwind CSS berikut yang digunakan untuk membuat sudut elemen menjadi melengkung?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'border-radius', 'correct' => false],
                    ['text' => 'rounded-lg', 'correct' => true],
                    ['text' => 'corner-smooth', 'correct' => false],
                    ['text' => 'flex-rounded', 'correct' => false],
                ]
            ],
        ];

        foreach ($questionsTest1 as $q) {$qId = DB::table('questions')->insertGetId([
                'test_id' => $test1Id,
                'question_text' => $q['text'],
                'score_weight' => $q['weight'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($q['choices'] as$c) {
                DB::table('choices')->insert([
                    'question_id' => $qId,
                    'choice_text' => $c['text'],
                    'is_correct' => $c['correct'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ==========================================
        // TEST 2: Framework Laravel 12 & Modern Backend
        // ==========================================
        $test2Id = DB::table('tests')->insertGetId([
            'title' => 'Tes Backend Framework Laravel',
            'description' => 'Menguji arsitektur MVC, Eloquent ORM, Middleware, Routing, dan Database Transactions.',
            'duration_minutes' => 45,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $questionsTest2 = [
            [
                'text' => 'Di Laravel, method Eloquent mana yang digunakan untuk memperbarui record jika ada, atau membuat record baru jika belum ada?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'firstOrCreate()', 'correct' => false],
                    ['text' => 'updateOrCreate()', 'correct' => true],
                    ['text' => 'saveOrUpdate()', 'correct' => false],
                    ['text' => 'upsertOnly()', 'correct' => false],
                ]
            ],
            [
                'text' => 'Apa nama problem performa query database saat melakukan pemanggilan relasi di dalam perulangan tanpa Eager Loading?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'Memory Leak Query', 'correct' => false],
                    ['text' => 'N+1 Query Problem', 'correct' => true],
                    ['text' => 'Deadlock State', 'correct' => false],
                    ['text' => 'Cascade Loop Issue', 'correct' => false],
                ]
            ],
            [
                'text' => 'Manakah perintah Artisan yang digunakan untuk menghapus semua tabel dan menjalankan seluruh migrasi beserta seedernya dari awal?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'php artisan migrate:reset', 'correct' => false],
                    ['text' => 'php artisan migrate:rollback --seed', 'correct' => false],
                    ['text' => 'php artisan migrate:fresh --seed', 'correct' => true],
                    ['text' => 'php artisan db:wipe', 'correct' => false],
                ]
            ],
            [
                'text' => 'Fitur apakah di Laravel yang berfungsi mencegat dan memeriksa HTTP request sebelum masuk ke Controller?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'Provider', 'correct' => false],
                    ['text' => 'Middleware', 'correct' => true],
                    ['text' => 'Facade', 'correct' => false],
                    ['text' => 'Resource Controller', 'correct' => false],
                ]
            ],
            [
                'text' => 'Apa kegunaan dari DB::transaction() di Laravel?',
                'weight' => 10,
                'choices' => [
                    ['text' => 'Mempercepat koneksi database', 'correct' => false],
                    ['text' => 'Memastikan serangkaian query database berhasil semua atau dibatalkan (rollback) seluruhnya jika terjadi error', 'correct' => true],
                    ['text' => 'Mengubah query SQL menjadi format JSON otomatis', 'correct' => false],
                    ['text' => 'Melakukan eksekusi query secara terenkripsi', 'correct' => false],
                ]
            ],
        ];

        foreach ($questionsTest2 as $q) {$qId = DB::table('questions')->insertGetId([
                'test_id' => $test2Id,
                'question_text' => $q['text'],
                'score_weight' => $q['weight'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($q['choices'] as$c) {
                DB::table('choices')->insert([
                    'question_id' => $qId,
                    'choice_text' => $c['text'],
                    'is_correct' => $c['correct'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}