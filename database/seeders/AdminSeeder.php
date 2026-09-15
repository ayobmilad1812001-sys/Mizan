<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        if ($adminRole->users()->exists()) {
            $this->command?->warn('An admin account already exists. Skipped.');

            return;
        }

        if ($this->command === null || $this->command->option('no-interaction')) {
            throw new RuntimeException('AdminSeeder is interactive. Run: php artisan db:seed --class=AdminSeeder');
        }

        do {
            $input = [
                'name' => $this->command->ask('Admin name', 'مدير النظام'),
                'email' => $this->command->ask('Admin email'),
                'password' => $this->command->secret('Password (min 12 characters, letters and numbers)'),
                'password_confirmation' => $this->command->secret('Confirm password'),
            ];

            $validator = Validator::make($input, [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
            ]);

            foreach ($validator->errors()->all() as $error) {
                $this->command->error($error);
            }
        } while ($validator->fails());

        $admin = new User;
        $admin->forceFill([
            'role_id' => $adminRole->id,
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'status' => UserStatus::Active,
        ])->save();

        $this->command->info("Admin account created: {$admin->email}");
    }
}
