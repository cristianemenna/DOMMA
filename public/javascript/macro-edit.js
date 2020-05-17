$(document).ready(function () {

    // Si l'un des boutons du formulaire de choix de macros est séléctionné
    $('#macro-edit-form input').click(function() {
        // Récupère l'attribut id du input choisi
        var buttonName = $(this).attr("id");
        $('#macro-edit-form').submit(function (e) {
            // Si le choix était pour "voir en détail" : empêche l'envoie du formulaire
            if (buttonName == "macro-details") {
                e.preventDefault();

                if ($('#modal-message')) {
                    $('#modal-message').empty();
                    $('#modal-message').css('display', 'none');
                }
                $('#overlay').toggle();
                $('#modal').toggle();
                $('#modal-edit').toggle();

                // Requête en AJAX pour récupèrer les informations de la macro choisie
                sendMacroId($('#macro_apply_macro').val())
            }

            buttonName = null;
        });
    });

   // Toggle du modal et messages lors d'un clic sur bouton "annuler" ou icon de fermeture
    $('#modal-cancel').click(function () {
       $('#modal-message').empty();
       $('#overlay').toggle();
       $('#modal').toggle();
       $('#modal-edit').toggle();
   });

    $('#icon-close-modal').click(function () {
        $('#modal-message').empty();
        $('#overlay').toggle();
        $('#modal').toggle();
        $('#modal-edit').toggle();
    });

    // Crée un JSON avec les valeus du formulaire lors de son envoie
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

// Requête pour récupèrer les informations de la macro choisie sur la page show d'un import
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
            // Ajout des informations de l'objet macro sur le formulaire d'édition en modal
                macro = data;
                macroType = macro['type'];

                $('#macro-id').val(macro['id']);
                $('#macro-title').val(macro['title']);
                $('#macro-description').val(macro['description']);
                $('#macro-code').val(macro['code']);
                $('#macro-type').val(macroType);
        },

        error: function (xhr) {
        }
    });
}

// Envoi les modifications du formulaire d'édition de Macro ouvert en modal
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
            receiveEditData('success');
        },

        error: function (xhr) {
            receiveEditData('error');
        }
    });
}

// Gestion de l'affichage de modal suite à la réponse
function receiveEditData(status) {

    // Crée un message selon type de réponse
    if (status === 'success') {
        var textContent = $("<p></p>").text("La macro a bien été modifiée.");
        var icon = $("<i class='fas fa-check'></i>");
    } else {
        var textContent = $("<p></p>").text("La macro n'a pas pu être modifiée.");
        var icon = $("<i class='fas fa-exclamation-circle'></i>");
    }

    // Crée la div qui contiendra le message si elle n'existe pas encore
    if (!$('#modal-message').length) {
        var messageContainer = $("<div></div>").attr('id', 'modal-message');
        $('#overlay').append(messageContainer);
    } else {
        $('#modal-message').toggle();
    }

    $('#modal-message').append(textContent);
    $('#modal-message').append(icon);

    $('#modal').toggle(500);
    $('#modal-edit').toggle(500);
    $('#overlay').delay(1500).fadeOut();
}