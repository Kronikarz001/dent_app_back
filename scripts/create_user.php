<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$kernel->bootstrap();

$user = new User;
$user->first_name = getenv('CREATE_FIRST_NAME');
$user->last_name = getenv('CREATE_LAST_NAME');
$user->email = getenv('CREATE_EMAIL');
$user->password = Hash::make(getenv('CREATE_PASSWORD'));
$user->pesel = getenv('CREATE_PESEL');
$user->is_admin = getenv('CREATE_IS_ADMIN');

if (User::where('email', $user->email)->exists() || User::where('pesel', $user->pesel)->exists()) {
    echo "User with this credentials already exists.\n";
    exit;
}

$user->save();

echo "User created successfully.\n";
