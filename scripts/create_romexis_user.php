<?php

use App\Models\Permission;
use App\Models\PermissionAssignment;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$kernel->bootstrap();

$email = getenv('ROMEXIS_USER_EMAIL');
$password = getenv('ROMEXIS_USER_PASSWORD');
$pesel = getenv('ROMEXIS_USER_PESEL');

$user = User::where('email', $email)->first();

if (! $user) {
    if (User::where('pesel', $pesel)->exists()) {
        echo "User with this PESEL already exists.\n";
        exit;
    }

    $user = new User;
    $user->first_name = 'Romexis';
    $user->last_name = 'Bridge';
    $user->email = $email;
    $user->pesel = $pesel;
    $user->is_admin = false;
    $user->is_active = true;
}

$user->password = Hash::make($password);
$user->save();

foreach (['view', 'edit'] as $type) {
    $permission = Permission::where('resource', 'patientphoto')->where('type', $type)->first();

    if (! $permission) {
        continue;
    }

    PermissionAssignment::firstOrCreate([
        'grantable_type' => Permission::class,
        'grantable_id' => $permission->uuid,
        'assignable_type' => User::class,
        'assignable_id' => $user->uuid,
    ]);
}

echo "Romexis user created/updated successfully.\n";
