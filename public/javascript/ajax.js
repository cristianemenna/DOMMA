console.log('ready');

$(document).ready(function () {
    var form = $("#macro_apply").parent().get(0);

    $(form).submit(function (e) {
        e.preventDefault();
        $('#overlay').css("display", "block");
        $('#modal-cancel').on('click', function () {
            $('#overlay').css("display", "none");
        })

        sendMacroId($('#macro_apply_macro').val())
    });
});

function sendMacroId(macroId) {
    $.ajax({
        type: 'POST',
        url: '/macro/' + macroId + '/ajax',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data, status) {
            // $('#popup').html(data);
            console.log(data);
            for (i = 0; i < 4; i++) {
                macro = data;

                $('#macro-title').val(macro['title']);
                $('#macro-description').val(macro['description']);
                $('#macro-code').val(macro['code']);
                $('#macro-type').val(macro['type']);
            }
        },

        error: function (xhr, textStatus, errorThrown) {
            console.log(xhr);
        }
    });
}