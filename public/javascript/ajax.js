var modalContainer = $('#overlay');
var modal = $('#modal');
var modalMessage = $('#modal-message');
var modalForm = $('#modal-edit');
var modalFormCancel = $('#modal-cancel');

$(document).ready(function () {

    // Si l'un des boutons du formulaire de choix de macros est séléctionné
    $('#macro-edit-form input').click(function() {
        // Récupère l'attribut id du input choisi
        var buttonName = $(this).attr("id");
        $('#macro-edit-form').submit(function (e) {
            // Si le choix était pour "voir en détail" : empêche l'envoie du formulaire
            if (buttonName == "macro-details") {
                e.preventDefault();

                modalMessage.empty();
                modalMessage.toggle();
                modalContainer.toggle();
                modal.toggle();
                modalForm.toggle();

                // Requête en AJAX pour récupèrer les informations de la macro choisie
                sendMacroId($('#macro_apply_macro').val())
            }

            buttonName = null;
        });
    });

   // Toggle du modal et messages lors d'un clic sur bouton "annuler" ou icon de fermeture
   modalFormCancel.click(function () {
       modalMessage.empty();
       modalContainer.toggle();
       modal.toggle();
       modalForm.toggle();
   });

    $('#icon-close-modal').click(function () {
        modalMessage.empty();
        modalContainer.toggle();
        modal.toggle();
        modalForm.toggle();
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
            // Ajout d'un message de confirmation si la macro a pu être modifiée.
            var textContent = $("<p></p>").text("La macro a bien été modifiée.");

            modal.toggle(500);
            modalForm.toggle();
            modalMessage.append(textContent);
            modalMessage.append($("<i class='fas fa-check'></i>"));
            modalMessage.toggle();
            modalContainer.delay(1500).fadeOut();
        },

        error: function (xhr) {
            // Ajout d'un message d'erreur si la macro n'a pas pu être modifiée.
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