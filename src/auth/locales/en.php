<?php

function auth_en() {
  return
    array("auth.menu.title"          => "Admin",
        "auth.menu.users"            => "Users",
        "auth.menu.roles"            => "Roles",
        "auth.menu.mail"             => "Email",
        "auth.menu.password"         => "Password",
        "auth.menu.login"            => "Login",
        "auth.menu.logout"           => "Logout",
        "auth.menu.recover"          => "Forgot your password ?",
        "auth.menu.fail"             => "Login error",
        "auth.menu.fail.credentials" => "Unknown user name or bad password.",
        "auth.menu.recover"          => "Recover password.",



        "auth.perm.user.view"      => "Consult user list",
        "auth.perm.user.create"    => "Create new user",
        "auth.perm.user.update"    => "Update existing user",
        "auth.perm.user.terminate" => "Delete user",

        "auth.perm.role.view"      => "Consult role list",
        "auth.perm.role.create"    => "Create new role",
        "auth.perm.role.update"    => "Update existing role",
        "auth.perm.role.terminate" => "Delete role",

        "auth.user.list.name"            => "Name",
        "auth.user.list.actions"         => "Actions",
        "auth.user.list.role"            => "Roles",
        "auth.user.list.tooltips.delete" => "Delete current user",
        "auth.user.list.tooltips.edit"   => "Edit current user",
        "auth.user.list.add"             => "Add user",
        "auth.user.list.edit"            => "Edit this user",
        "auth.user.list.delete"          => "Delete this user",
        "auth.user.list.delete.confirm"  => "Are you sure ?",

        "auth.user.add.title"                => "New user",
        "auth.user.add.name"                 => "First name / Last name",
        "auth.user.add.password"             => "Password",
        "auth.user.add.password_confirm"     => "Confirm password",
        "auth.user.add.error.alreadyexists"  => "User mail '%s' already exists",

        "auth.user.recover.error.notfound" => "The address %s was not found.",
        "auth.user.recover.ok"             => "Hi %s, A new password has been sent to your address %s;.",
        "auth.user.recover.submit"         => "Send new password !",

        "auth.role.list.add"             => "Add role",
        "auth.role.list.name"            => "Name",
        "auth.role.list.data"            => "Data type",
        "auth.role.list.delete"          => "Delete this role",
        "auth.role.list.delete.confirm"  => "Are you sure ?",
        "auth.role.list.edit"            => "Edit this role",

        "auth.error.loginrequiered"       => "You must be logged in to access this page. Please login.",
        "auth.error.unauthorized"         => "You are not authorized to view this page."
    );
}

?>