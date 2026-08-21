<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterPermissionAdminRoutePermissionsSeeder
 *
 * Rejestruje route'y samego systemu uprawnień (permission/permission-group/
 * user-group/role/role-group) pod ten sam mechanizm view/edit. `role.delegate`
 * i `role-group.delegate` są celowo pominięte — te dwie trasy mają własną,
 * bespoke autoryzację w RoleService/RoleGroupService::delegate() (status
 * kierownika + posiadanie delegowanego uprawnienia) i są zawsze dostępne dla
 * zalogowanego użytkownika (patrz PermissionMiddleware::ALWAYS_ALLOWED).
 */
class RegisterPermissionAdminRoutePermissionsSeeder extends Seeder
{
    use RegistersRoutePermissions;

    /**
     * @return array<string, string[]>
     */
    protected function resourceRoutes(): array
    {
        return [
            'permission' => [
                'permission.index',
                'permission.selectList',
                'permission.show',
            ],
            'permission-group' => [
                'permission-group.index',
                'permission-group.store',
                'permission-group.selectList',
                'permission-group.show',
                'permission-group.update',
                'permission-group.destroy',
                'permission-group.assignPermissions',
            ],
            'user-group' => [
                'user-group.index',
                'user-group.store',
                'user-group.selectList',
                'user-group.show',
                'user-group.update',
                'user-group.destroy',
                'user-group.assignUsers',
                'user-group.assignPermissions',
            ],
            'role' => [
                'role.index',
                'role.store',
                'role.selectList',
                'role.show',
                'role.update',
                'role.destroy',
                'role.assignUsers',
                'role.assignPermissions',
            ],
            'role-group' => [
                'role-group.index',
                'role-group.store',
                'role-group.selectList',
                'role-group.show',
                'role-group.update',
                'role-group.destroy',
                'role-group.assignRoles',
                'role-group.assignUsers',
                'role-group.assignPermissions',
            ],
        ];
    }
}
