$(document).ready(function() {

    // User : select rôle
    $('#users_role').select2({
        allowClear: true,
        placeholder: "Rôle"
    });

    // Macro : création/édition, choix de type
    $('#macro_type').select2({
        placeholder: "Type",
        allowClear: true
    });

    // Macro : application
    $('#macro_apply_macro').select2({
        allowClear: false,
    });

    // Import : suppression de colonnes
    $('#macro_columns_columns').select2({
        allowClear: true,
        placeholder: "Choisir colonnes"
    });

    // Import : export de fichier
    $('#export_fileType').select2({
        allowClear: false,
    });

    $('#share_context_users').select2({
        allowClear: false,
        placeholder: "Partager contexte"
    });
});
