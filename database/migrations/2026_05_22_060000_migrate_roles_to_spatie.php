<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan roles sudah ada di tabel Spatie
        $roles = ['admin', 'mandor', 'staff_operator'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Migrasi data: assign Spatie role sesuai kolom `role` di users
        $users = DB::table('users')->whereNotNull('role')->get();

        foreach ($users as $userData) {
            $user = User::find($userData->id);
            if ($user && $userData->role) {
                $role = Role::where('name', $userData->role)->where('guard_name', 'web')->first();
                if ($role && !$user->hasRole($userData->role)) {
                    $user->assignRole($userData->role);
                }
            }
        }
    }

    public function down(): void
    {
        // Hapus semua role assignments dari model_has_roles
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->delete();
    }
};
