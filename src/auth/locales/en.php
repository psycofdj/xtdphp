<?php

function auth_en() {
  return
    array("auth.menu.title"    => "Admin",
          "auth.menu.users"    => "Users",
          "auth.menu.roles"    => "Roles",
          "auth.menu.mail"     => "Email",
          "auth.menu.password" => "Password",
          "auth.menu.login"    => "Login",
          "auth.menu.logout"   => "Logout",
          "auth.menu.recover"  => "Forgot your password ?",
          "auth.menu.wrong"    => "Unknown user name or bad password.",

          "auth.roles.user.read"  => "consult user list",
          "auth.roles.user.write" => "edit/add/remove a user",

          "auth.rolelist.tag"               => "Tag",
          "auth.rolelist.description"       => "Description",

          "auth.userlist.name" => "Name",
          "auth.userlist.actions"   => "Actions",
          "auth.userlist.role"   => "Roles",
          "auth.userlist.tooltips.delete"   => "Delete current user",
          "auth.userlist.tooltips.edit"     => "Edit current user",
          );
}

?>