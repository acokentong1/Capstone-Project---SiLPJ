<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('silpj:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("Akun dengan email {$email} tidak ditemukan.");
        return 1;
    }

    if ($user->role === 'admin') {
        $this->info("{$email} sudah memiliki role admin.");
        return 0;
    }

    User::withoutEvents(function () use ($user): void {
        $user->forceFill(['role' => 'admin', 'is_active' => true])->save();
    });

    $this->info("{$email} sekarang menjadi admin SILPJ.");
    return 0;
})->purpose('Promosikan akun SILPJ menjadi admin');

Artisan::command('silpj:remove-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("Akun dengan email {$email} tidak ditemukan.");
        return 1;
    }

    if ($user->role !== 'admin') {
        $this->info("{$email} bukan admin.");
        return 0;
    }

    if (User::where('role', 'admin')->count() <= 1) {
        $this->error('Admin terakhir tidak dapat diturunkan melalui command ini.');
        return 1;
    }

    User::withoutEvents(function () use ($user): void {
        $user->forceFill(['role' => 'user'])->save();
    });

    $this->info("Role admin {$email} berhasil dicabut.");
    return 0;
})->purpose('Turunkan role admin SILPJ menjadi user');
