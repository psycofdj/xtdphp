/* Set the defaults for DataTables initialisation */
$.extend( true, $.fn.dataTable.defaults, {
  "oLanguage": {
    "sProcessing":     "Traitement en cours...",
    "sSearch":         "Rechercher",
    "sLengthMenu":     "_MENU_ par page",
    "sInfo":           "&Eacute;lement _START_ &agrave; _END_ sur _TOTAL_",
    "sInfoEmpty":      "&Eacute;lement 0 &agrave; 0 sur 0",
    "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
    "sInfoPostFix":    "",
    "sLoadingRecords": "Chargement en cours...",
    "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
    "sEmptyTable":     "Aucune donnée disponible dans le tableau",
    "oPaginate": {
      "sFirst":      "Premier",
      "sPrevious":   "Pr&eacute;c&eacute;dent",
      "sNext":       "Suivant",
      "sLast":       "Dernier"
    },
    "oAria": {
      "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
      "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
    }
  }
});
