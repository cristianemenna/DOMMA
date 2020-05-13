$(document).ready( function () {
    $('#admin-table').DataTable({
        "scrollX": true,
        "language": {
            "sSearch": "Rechercher :",
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
    });
});


