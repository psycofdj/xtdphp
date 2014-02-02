<?php

function auth_fr() {
  return
    array("auth.menu.title"    => "Admin",
          "auth.menu.users"    => "Utilisateurs",
          "auth.menu.roles"    => "Rôles",
          "auth.menu.mail"     => "Email",
          "auth.menu.password" => "Mot de passe",
          "auth.menu.login"    => "Connexion",
          "auth.menu.logout"   => "Déconnexion",
          "auth.menu.recover"  => "Mot de passe perdu ?",
          "auth.menu.wrong"    => "L'email ou le mot de passe saisi est incorrect.",

          "auth.roles.user.read"  => "consulter la liste des utilisateurs",
          "auth.roles.user.write" => "éditer/créer/modifier un utilisateur",

          "auth.rolelist.tag"               => "Étiquette",
          "auth.rolelist.description"       => "Description",

          "auth.userlist.name"              => "Nom",
          "auth.userlist.actions"           => "Actions",
          "auth.userlist.role"              => "Rôles",
          "auth.userlist.tooltips.delete"   => "Supprimer l'utilisateur",
          "auth.userlist.tooltips.edit"     => "Éditer l'utilisateur",


          );
}

?>