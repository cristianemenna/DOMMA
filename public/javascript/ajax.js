console.log('ready');

$(document).ready(function () {
    $('#macro-edit-form input').click(function(e) {
        var buttonName = $(this).attr("id");
        $('#macro-edit-form').submit(function (e) {
            if (buttonName == "macro-details") {
                e.preventDefault();

                $('#overlay').css("display", "block");
                $('#modal-cancel').on('click', function () {
                    $('#overlay').css("display", "none");
                });

                sendMacroId($('#macro_apply_macro').val())
            }

            buttonName = null;
        });

        $('#modal-edit').submit(function () {
            e.preventDefault();
            sendMacroChanges();
        })
    })
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

        success: function (data) {
            console.log(data);
            for (i = 0; i < 4; i++) {
                macro = data;

                $('#macro-id').val(macro['id']);
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

function sendMacroChanges(macroId) {
    $.ajax({
        type: 'POST',
        url: '/macro/' + macroId + '/ajax',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
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