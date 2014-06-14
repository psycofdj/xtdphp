<?php

function auth_fr() {
  return
    array("auth.menu.title"          => "Admin",
        "auth.menu.users"            => "Utilisateurs",
        "auth.menu.roles"            => "Rôles",
        "auth.menu.mail"             => "Email",
        "auth.menu.password"         => "Mot de passe",
        "auth.menu.login"            => "Connexion",
        "auth.menu.logout"           => "Déconnexion",
        "auth.menu.recover"          => "Mot de passe perdu ?",
        "auth.menu.fail"             => "Erreur d'authentification",
        "auth.menu.fail.credentials" => "L'email ou le mot de passe saisi est incorrect.",
        "auth.menu.recover"          => "Récupération de mot de passe.",

        "fds"        => "Fermer",

        "auth.perm.user.view"      => "Consulter la liste des utillisateurs",
        "auth.perm.user.create"    => "Créer un utilisateur",
        "auth.perm.user.update"    => "Modifier un utilisateur",
        "auth.perm.user.terminate" => "Supprimer un utilisateur",

        "auth.perm.role.view"      => "Consulter la liste des rôles",
        "auth.perm.role.create"    => "Créer un rôle",
        "auth.perm.role.update"    => "Modifier un rôle",
        "auth.perm.role.terminate" => "Supprimer un rôle",

        "auth.user.list.name"              => "Nom",
        "auth.user.list.actions"           => "Actions",
        "auth.user.list.role"              => "Rôles",
        "auth.user.list.tooltips.delete"   => "Supprimer l'utilisateur",
        "auth.user.list.tooltips.edit"     => "Éditer l'utilisateur",
        "auth.user.list.add"               => "Ajouter un utilisateur",
        "auth.user.list.edit"              => "Editer cet utilisateur",
        "auth.user.list.delete"            => "Supprimer cet utilisateur",
        "auth.user.list.delete.confirm"    => "Êtes vous sûr ?",

        "auth.user.add.title"                  => "Nouvel utilisateur",
        "auth.user.add.name"                   => "Prénom / Nom",
        "auth.user.add.password"               => "Mot de passe",
        "auth.user.add.password_confirm"       => "Confirmer mot de passe",

        "auth.user.recover.error.notfound" => "L'adresse %s n'a pas été trouvée.",
        "auth.user.recover.ok"             => "Bonjour %s, un nouveau mot de passe a été envoyé à votre adresse %s.",
        "auth.user.recover.submit"         => "Envoyer un nouveau mot de passe !",

        "auth.role.list.add"             => "Ajouter un rôle",
        "auth.role.list.name"            => "Nom",
        "auth.role.list.data"            => "Type de donnée",
        "auth.role.list.delete"          => "Supprimer ce rôle",
        "auth.role.list.delete.confirm"  => "Êtes vous sûr ?",
        "auth.role.list.edit"            => "Èditer ce rôle",

        "auth.error.loginrequiered"       => "Vous devez être identifié pour accèder à cette page. Veuillez vous connecter.",
        "auth.error.unauthorized"         => "Vous n'êtes pas autorisé à consulter cette page."
    );
}

?>