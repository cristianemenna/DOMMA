$(document).ready(function() {
    $('#users_roles').select2({
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
        placeholder: "Appliquer macro"
    });
});
