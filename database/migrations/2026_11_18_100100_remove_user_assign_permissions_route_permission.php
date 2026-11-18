<?php

use App\Models\PermissionRoute;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        PermissionRoute::query()
            ->where('route_name', 'user.assignPermissions')
            ->get()
            ->each(fn (PermissionRoute $route) => $route->delete());
    }
};
