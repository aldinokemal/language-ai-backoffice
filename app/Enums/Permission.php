<?php

namespace App\Enums;

use App\Classes\EnumConcern;

enum Permission: string
{
    use EnumConcern;

    // Training Class
    case LANGUAGE_AI_USERS_VIEW = 'language.ai.users.view';
    case LANGUAGE_AI_USERS_CREATE = 'language.ai.users.create';
    case LANGUAGE_AI_USERS_UPDATE = 'language.ai.users.update';
    case LANGUAGE_AI_USERS_DELETE = 'language.ai.users.delete';

    // System Users
    case SYSTEM_USERS_VIEW = 'system.users.view';
    case SYSTEM_USERS_CREATE = 'system.users.create';
    case SYSTEM_USERS_UPDATE = 'system.users.update';
    case SYSTEM_USERS_DELETE = 'system.users.delete';

    // System Roles
    case SYSTEM_ROLES_VIEW = 'system.roles.view';
    case SYSTEM_ROLES_CREATE = 'system.roles.create';
    case SYSTEM_ROLES_UPDATE = 'system.roles.update';
    case SYSTEM_ROLES_DELETE = 'system.roles.delete';

    // System Permissions
    case SYSTEM_PERMISSIONS_VIEW = 'system.permissions.view';
    case SYSTEM_PERMISSIONS_CREATE = 'system.permissions.create';
    case SYSTEM_PERMISSIONS_UPDATE = 'system.permissions.update';
    case SYSTEM_PERMISSIONS_DELETE = 'system.permissions.delete';

    // System Organizations
    case SYSTEM_ORGANIZATIONS_VIEW = 'system.organizations.view';
    case SYSTEM_ORGANIZATIONS_CREATE = 'system.organizations.create';
    case SYSTEM_ORGANIZATIONS_UPDATE = 'system.organizations.update';
    case SYSTEM_ORGANIZATIONS_DELETE = 'system.organizations.delete';

    // System Menus
    case SYSTEM_MENUS_VIEW = 'system.menus.view';
    case SYSTEM_MENUS_CREATE = 'system.menus.create';
    case SYSTEM_MENUS_UPDATE = 'system.menus.update';
    case SYSTEM_MENUS_DELETE = 'system.menus.delete';


}
