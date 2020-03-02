<?php

namespace App\Libraries;
use App\Interfaces\UserAccessManager;
use App\Role;
class AccessManagerService implements UserAccessManager
{
    public function getRoleId($roleName)
    {
        return Role::where('name', $roleName)->firstOrFail()->id;
    }
}
