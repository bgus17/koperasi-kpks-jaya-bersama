<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Spatie Verification ===" . PHP_EOL;

$roles = \Spatie\Permission\Models\Role::pluck('name');
echo "Roles: " . $roles->join(', ') . PHP_EOL;

$permCount = \Spatie\Permission\Models\Permission::count();
echo "Permissions count: " . $permCount . PHP_EOL;

$users = ['admin@koperasi.com', 'ketua@koperasi.com', 'mandor@koperasi.com', 'staff@koperasi.com'];
foreach ($users as $email) {
    $user = \App\Models\User::where('email', $email)->first();
    if ($user) {
        echo "{$email} => Spatie roles: " . $user->getRoleNames()->join(', ') . " | DB role col: " . ($user->role ?? 'null') . PHP_EOL;
    } else {
        echo "{$email} => USER NOT FOUND" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Route Middleware Check ===" . PHP_EOL;
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$checked = ['pendapatan.index', 'karyawan.index', 'rekap.index', 'login', 'admin.login'];
foreach ($checked as $name) {
    $route = $routes->getByName($name);
    if ($route) {
        echo "{$name} => middleware: " . implode(', ', $route->middleware()) . PHP_EOL;
    }
}

echo PHP_EOL . "Done!" . PHP_EOL;
