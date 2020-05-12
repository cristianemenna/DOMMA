$(document).ready( function () {

    // N'affiche pas les colonnes supprimées en tant qu'item séléctionné lors du rechargement de la page
    $('.select2-selection__choice').remove();

    // Affiche l'import selon données de la BDD
    $('#file-table').DataTable( {
            "scrollX": true,
            "language": {
                "sSearch": "Rechercher dans le fichier:",
                "sLoadingRecords": "Nous sommes en train de charger les données...",
                "sLengthMenu":    "Afficher _MENU_ lignes",
                "oPaginate": {
                    "sFirst":    "Premier",
                    "sLast":    "Dernier",
                    "sNext":    "Suivant",
                    "sPrevious": "Précedent"
                },
                "sInfoFiltered":  "Filtré sur un total de _MAX_ registres",
                "sInfo":          "_START_ à _END_ lignes sur un total de _TOTAL_ lignes",
                "sEmptyTable":    "Aucune information disponible dans cette table",
                "sZeroRecords":   "Aucun résultat disponible",
            }
        }
    );
});


