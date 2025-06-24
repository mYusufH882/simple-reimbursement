<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // ADMIN USER
        // ==========================================
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        // ==========================================
        // MANAGER USERS
        // ==========================================
        User::create([
            'name' => 'Manager Keuangan',
            'email' => 'manager@mail.com',
            'password' => Hash::make('manager123'),
            'role' => User::ROLE_MANAGER,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Manager HR',
            'email' => 'manager.hr@mail.com',
            'password' => Hash::make('manager123'),
            'role' => User::ROLE_MANAGER,
            'email_verified_at' => now(),
        ]);

        // ==========================================
        // EMPLOYEE USERS
        // ==========================================
        $employees = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'David Brown',
                'email' => 'david.brown@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Robert Garcia',
                'email' => 'robert.garcia@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Maria Rodriguez',
                'email' => 'maria.rodriguez@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'James Taylor',
                'email' => 'james.taylor@mail.com',
                'password' => Hash::make('employee123'),
            ],
            [
                'name' => 'Jennifer Lee',
                'email' => 'jennifer.lee@mail.com',
                'password' => Hash::make('employee123'),
            ]
        ];

        foreach ($employees as $employee) {
            User::create([
                'name' => $employee['name'],
                'email' => $employee['email'],
                'password' => $employee['password'],
                'role' => User::ROLE_EMPLOYEE,
                'email_verified_at' => now(),
            ]);
        }

        // ==========================================
        // mail USERS WITH SIMPLE CREDENTIALS
        // ==========================================

        // Simple admin for mail
        User::create([
            'name' => 'mail Admin',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        // Simple manager for mail
        User::create([
            'name' => 'mail Manager',
            'email' => 'manager@mail.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_MANAGER,
            'email_verified_at' => now(),
        ]);

        // Simple employee for mail
        User::create([
            'name' => 'mail Employee',
            'email' => 'employee@mail.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_EMPLOYEE,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Created ' . User::count() . ' users successfully!');
        $this->command->info('Demo credentials:');
        $this->command->info('Admin: admin@mail.com / password');
        $this->command->info('Manager: manager@mail.com / password');
        $this->command->info('Employee: employee@mail.com / password');
    }
}
