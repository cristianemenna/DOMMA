$(document).ready(function() {
    $('#users_role').select2({
        allowClear: true,
        placeholder: "Rôle"
    });
});

$(document).ready(function() {
    $('#macro_type').select2({
        placeholder: "Type",
        allowClear: true
    });
});

$(document).ready(function() {
    $('#macro_apply_macro').select2({
        allowClear: false,
    });
});

$(document).ready(function() {
    $('#macro_columns_columns').select2({
        allowClear: true,
        placeholder: "Choisir colonnes"
    });

    $('#export_fileType').select2({
        allowClear: false,
    });
});
