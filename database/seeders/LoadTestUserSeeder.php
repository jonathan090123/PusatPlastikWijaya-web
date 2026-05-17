<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LoadTestUserSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('workspace/jmeter/users_200.csv');

        if (!is_file($csvPath)) {
            $this->command?->error("CSV file not found: {$csvPath}");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command?->error("Unable to open CSV file: {$csvPath}");
            return;
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            $this->command?->error('CSV file is empty.');
            return;
        }

        $header = array_map(static fn ($value) => trim((string) $value), $header);
        $expectedHeader = ['email', 'password'];
        if ($header !== $expectedHeader) {
            fclose($handle);
            $this->command?->error('CSV header must be: email,password');
            return;
        }

        $imported = 0;
        $timestamp = Carbon::now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $email = trim((string) $row[0]);
            $password = trim((string) $row[1]);

            if ($email === '' || $password === '') {
                continue;
            }

            preg_match('/(\d+)/', $email, $matches);
            $sequence = isset($matches[1]) ? str_pad($matches[1], 3, '0', STR_PAD_LEFT) : str_pad((string) ($imported + 1), 3, '0', STR_PAD_LEFT);

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "Load Test {$sequence}",
                    'phone' => '0812' . str_pad($sequence, 8, '0', STR_PAD_LEFT),
                    'address' => "Jl. Load Test No. {$sequence}, Blitar",
                    'city_type' => 'blitar',
                    'customer_type' => 'personal',
                    'business_name' => null,
                    'business_verified' => null,
                    'role' => 'customer',
                    'points' => 0,
                    'is_active' => true,
                    'email_verified_at' => $timestamp,
                    'password' => $password,
                ]
            );

            $imported++;
        }

        fclose($handle);

        $this->command?->info("Imported or updated {$imported} load test users.");
    }
}