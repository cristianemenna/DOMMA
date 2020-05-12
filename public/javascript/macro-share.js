$(document).ready(function () {

    // Si l'un des boutons du formulaire de choix de macros est séléctionné
    $('#macro-edit-form input').click(function() {
        // Récupère l'attribut id du input choisi
        var buttonName = $(this).attr("id");
        $('#macro-edit-form').submit(function (e) {
            // Si le choix était pour "macro-share" : empêche l'envoi du formulaire
            if (buttonName == "macro-share") {
                e.preventDefault();
                sendMacroId($('#macro_apply_macro').val())
            }

            buttonName = null;
        });
    });

    // Toggle du modal lors d'un clic sur bouton "annuler" ou icon de fermeture
    $('#cancel-share-context').click(function () {
        $('#share-context-modal-message').empty();
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
        $('#share-container').empty();
    });

    $('#icon-share-context-modal').click(function () {
        $('#share-context-modal-message').empty();
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
        $('#share-container').empty();
    });

    $('#share-form').submit(function(e) {
        e.preventDefault();
        formData = $('#share_macro_users').val();
        sendShareMacro(formData, $('#macro-id').val());
    })

});

// Récupère le template avec formulaire de partage de Macro
function sendMacroId(macroId) {
    $.ajax({
        type: 'GET',
        url: '/macro/' + macroId + '/share',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
            // Template twig en réponse
            decoded = JSON.parse(data);

            if ($('#share-container').html().length === 0 ) {
                $('.overlay-message').remove();
                $('#share-container').append(decoded);
                $('#share-context-modal-message').empty();
                $('#share-context-modal-message').toggle();
                $('#share-context-overlay').toggle();
                $('#share-context-modal').toggle();
            }

            // Permet d'actualiser l'information des fichiers .js pour bien retrouver les nouveaux éléments
            function reload_js(src) {
                $('script[src="' + src + '"]').remove();
                $('<script>').attr('src', src).appendTo('head');
            }

            reload_js('/javascript/macro-share.js');
            reload_js('/javascript/selects.js');
            reload_js('/select/js/select2.js');

            // Cache l'id de la Macro choisie dans le DOM
            var macroIdInput = $("<input></input>").val(macroId);
            macroIdInput.css('display', 'none');
            macroIdInput.attr('id', 'macro-id')
            $('#share-container').append(macroIdInput);
        },

        error: function (xhr) {
            console.log('erreur GET');
        }
    });
}

// Envoi des informations choisies sur le formulaire de partage de Macro
function sendShareMacro(formData, macroId) {
    $.ajax({
        type: 'POST',
        url: '/macro/' + macroId + '/share',
        data: JSON.stringify(formData),
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
            receiveData('success');
        },

        error: function (xhr) {
            receiveData('error');
        }
    });
};

// Gestion de l'affichage de modal suite à la réponse
function receiveData(status) {

    // Crée un message selon type de réponse
    if (status === 'success') {
        var textContent = $("<p></p>").text("La macro a bien été partageé.");
    } else {
        var textContent = $("<p></p>").text("La macro n'a pas pu être partageé.");
    }

    // Crée la div qui contiendra le message si elle n'existe pas encore
    if ($('.overlay-message').length) {
        $('.overlay-message').css('display', 'block');
    } else {
        var textContainer = $("<div></div>").addClass('overlay');
        var messageContainer = $("<div></div>").addClass('share-context-modal-message');

        $('main').append(textContainer);
        textContainer.addClass('overlay-message');
        textContainer.append(messageContainer);
        messageContainer.append(textContent)

        // Ajout d'icon selon type de message
        if (status === 'success') {
            messageContainer.append($("<i class='fas fa-check'></i>"));
        } else {
            messageContainer.append($("<i class='fas fa-exclamation-circle'></i>"));
        }

        textContainer.fadeIn();
        textContainer.delay(1500).fadeOut();
        $('#share-container').empty();
    }
}

