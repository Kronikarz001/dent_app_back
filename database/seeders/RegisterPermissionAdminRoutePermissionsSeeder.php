<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\RegistersRoutePermissions;
use Illuminate\Database\Seeder;

/**
 * Summary of RegisterPermissionAdminRoutePermissionsSeeder
 *
 * Rejestruje route'y samego systemu uprawnień (permission/permission-group/
 * user-group/role/department) pod ten sam mechanizm view/edit. `role.delegate`,
 * `department.delegate`, `department.createRole`, `user-group.createRole` są
 * celowo pominięte — te trasy mają własną, bespoke autoryzację w
 * RoleService/DepartmentService/UserGroupService (status kierownika +
 * posiadanie/nieprzekraczanie delegowanych/nadawanych uprawnień) i są zawsze
 * dostępne dla zalogowanego użytkownika (patrz PermissionMiddleware::ALWAYS_ALLOWED).
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
                'user-group.assignJobPositions',
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
            'department' => [
                'department.index',
                'department.store',
                'department.selectList',
                'department.show',
                'department.update',
                'department.destroy',
                'department.assignRoles',
                'department.assignJobPositions',
                'department.assignUsers',
                'department.assignPermissions',
            ],
        ];
    }
}
