<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Ambil ID semua test dan siswa
        $tests = DB::table('tests')->pluck('id');
        $students = DB::table('users')->where('role', 'student')->pluck('id');

        // ==========================================
        // EVENT 1: EVENT AKTIF (Bisa dikerjakan sekarang)
        // ==========================================
        $activeEventId = DB::table('events')->insertGetId([
            'title' => 'Ujian Seleksi Fullstack Web Developer 2026',
            'description' => 'Ujian komprehensif tahap 1 mencakup modul Web Dasar dan Backend Laravel. Wajib diselesaikan tepat waktu.',
            'start_time' => $now->copy()->subHours(2), // Mulai 2 jam lalu
            'end_time' => $now->copy()->addDays(2),    // Berakhir 2 hari lagi
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Attach SEMUA Test ke Event Aktif ini
        foreach ($tests as $testId) {
            DB::table('event_test')->insert([
                'event_id' => $activeEventId,
                'test_id' => $testId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Attach SEMUA Siswa ke Event Aktif ini
        foreach ($students as $studentId) {
            DB::table('event_user')->insert([
                'event_id' => $activeEventId,
                'user_id' => $studentId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ==========================================
        // EVENT 2: EVENT MENDATANG (Upcoming)
        // ==========================================
        $upcomingEventId = DB::table('events')->insertGetId([
            'title' => 'Sertifikasi Keahlian Vue.js & SPA (Mendatang)',
            'description' => 'Ujian sertifikasi khusus integrasi Inertia.js dan Vue 3 Composition API.',
            'start_time' => $now->copy()->addDays(3), // Mulai 3 hari lagi
            'end_time' => $now->copy()->addDays(5),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Attach Test 1 saja
        if (isset($tests[0])) {
            DB::table('event_test')->insert([
                'event_id' => $upcomingEventId,
                'test_id' => $tests[0],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($students as $studentId) {
            DB::table('event_user')->insert([
                'event_id' => $upcomingEventId,
                'user_id' => $studentId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ==========================================
        // EVENT 3: EVENT EXPIRED (Sudah Selesai/Lewat)
        // ==========================================
        $expiredEventId = DB::table('events')->insertGetId([
            'title' => 'Tryout Kualifikasi Awal (Expired)',
            'description' => 'Tryout simulasi sistem CBT yang telah dilaksanakan bulan lalu.',
            'start_time' => $now->copy()->subDays(10),
            'end_time' => $now->copy()->subDays(5),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (isset($tests[1])) {
            DB::table('event_test')->insert([
                'event_id' => $expiredEventId,
                'test_id' => $tests[1],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($students as $studentId) {
            DB::table('event_user')->insert([
                'event_id' => $expiredEventId,
                'user_id' => $studentId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}