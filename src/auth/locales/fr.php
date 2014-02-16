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


          "auth.perm.user.view"      => "Consulter la liste des utillisateurs",
          "auth.perm.user.create"    => "Créer un utilisateur",
          "auth.perm.user.update"    => "Modifier un utilisateur",
          "auth.perm.user.terminate" => "Supprimer un utilisateur",

          "auth.perm.role.view"      => "Consulter la liste des rôles",
          "auth.perm.role.create"    => "Créer un rôle",
          "auth.perm.role.update"    => "Modifier un rôle",
          "auth.perm.role.terminate" => "Supprimer un rôle",

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