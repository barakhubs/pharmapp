<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the Main Branch
        $mainBranch = Branch::create([
            'name' => 'Main Branch',
        ]);
        // Create an Admin User linked to the Main Branch
        $user = User::create([
            'username' => 'Admin',
            'email' => 'admin@pharmapp.test',
            'password' => bcrypt('Admin@321#'),
            'role' => 'admin',
            'branch_id' => $mainBranch->id,
        ]);

    }
}
