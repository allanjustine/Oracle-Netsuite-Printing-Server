<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = Role::create([
            "name"      =>      "admin",
        ]);

        $roleUser = Role::create([
            "name"      =>      "user",
        ]);

        $branch = Branch::create([
            "branch_code"   =>      "HO",
            "branch_name"   =>      "Head Office",
        ]);

        $user = User::create([
            "name"      =>      "Admin",
            "username"  =>      "admin",
            "email"     =>      "admin@gmail.com",
            "branch_id" =>      $branch->id,
            "password"  =>      "dapho04051983",
        ]);

        $user->assignRole($roleAdmin);
    }
}
