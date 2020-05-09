var modalContainer = $('#overlay');
var modal = $('#modal');
var modalMessage = $('#modal-message');
var modalForm = $('#modal-edit');

$(document).ready(function () {

    $('#macro-edit-form input').click(function() {
        var buttonName = $(this).attr("id");
        $('#macro-edit-form').submit(function (e) {
            if (buttonName == "macro-details") {
                e.preventDefault();

                modalMessage.empty();
                modalContainer.toggle();
                modal.toggle();
                modalForm.toggle();
                $('#modal-cancel').on('click', function () {
                    modalContainer.toggle();
                });

                sendMacroId($('#macro_apply_macro').val())
            }

            buttonName = null;
        });
    });

    $('#modal-edit').submit(function (e) {
        e.preventDefault();
        json =
            {
                id: $('#macro-id').val(),
                title: $('#macro-title').val(),
                description: $('#macro-description').val(),
                code: $('#macro-code').val(),
                type: $('#macro-type').val(),
            };
        jsonStringified = JSON.stringify(json, null, '\t');

        sendMacroChanges(
            jsonStringified,
            $('#macro-id').val(),
        );
    })
});

function sendMacroId(macroId) {
    $.ajax({
        type: 'GET',
        url: '/macro/' + macroId + '/ajax',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
            for (i = 0; i < 4; i++) {
                macro = data;

                $('#macro-id').val(macro['id']);
                $('#macro-title').val(macro['title']);
                $('#macro-description').val(macro['description']);
                $('#macro-code').val(macro['code']);
                $('#macro-type').val(macro['type']);
            }
        },

        error: function (xhr) {
        }
    });
}

function sendMacroChanges(formData, macroId) {
    $.ajax({
        type: 'POST',
        url: '/macro/' + macroId + '/ajax',
        data: formData,
        dataType: 'json',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
            var textContent = $("<p></p>").text("La macro a bien été modifiée.");

            modal.toggle(500);
            modalForm.toggle();
            modalMessage.append(textContent);
            modalMessage.append($("<i class='fas fa-check'></i>"));
            modalMessage.toggle();
            modalContainer.delay(1500).fadeOut();
        },

        error: function (xhr) {
            var textContent = $("<p></p>").text("La macro n'a pas pu être modifiée.");

            modal.toggle(500);
            modalForm.toggle();
            modalMessage.append(textContent);
            modal.append($("<i class='fas fa-exclamation-circle'></i>"));
            modalMessage.toggle();
            modalContainer.delay(1500).fadeOut();
        }
    });

}