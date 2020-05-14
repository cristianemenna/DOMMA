$(document).ready(function () {

    // Si l'un des boutons du formulaire de choix de macros est séléctionné
    $('#macro-edit-form input').click(function() {
        // Récupère l'attribut id du input choisi
        var inputId = $(this).attr("id");
        $('#macro-edit-form').submit(function (e) {
            // Si le choix était pour "macro-share" : empêche l'envoi du formulaire
            if (inputId == "macro-share") {
                e.preventDefault();
                sendMacroIdToShare($('#macro_apply_macro').val());
            }

            inputId = null;
        });
    });

    // Click depuis la page index des macros d'un utilisateur
    $('.macro-share').click(function() {
        // Récupère la valeur de l'input caché d'une macro, qui contient son id
        var macroId = $(this).children("input").val();
        sendMacroIdToShare(macroId);
    });

    // Toggle du modal lors d'un clic sur bouton "annuler" ou icon de fermeture
    $('#cancel-share-context').click(function () {
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
        $('#share-container').empty();
    });

    $('#icon-share-context-modal').click(function () {
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
        $('#share-container').empty();
    });

    $('#share-form').submit(function(e) {
        e.preventDefault();
        formData = $('#share_macro_users').val();
        receiveMacroForm(formData, $('.macro-id-hidden').text());
    })
});

// Récupère le template avec formulaire de partage de Macro
function sendMacroIdToShare(macroId) {
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
                $('#share-context-overlay').toggle();
                $('#share-context-modal').toggle();

                // Cache l'id de la Macro choisie dans le DOM
                var macroIdInput = $("<input>").text(macroId);
                macroIdInput.attr('type', 'hidden');
                macroIdInput.addClass('macro-id-hidden');
                $('#share-container').append(macroIdInput);
            }


            // Permet d'actualiser l'information des fichiers .js pour bien retrouver les nouveaux éléments
            function reload_js(src) {
                $('script[src="' + src + '"]').remove();
                $('<script>').attr('src', src).appendTo('head');
            }

            reload_js('/javascript/macro-share.js');
            reload_js('/javascript/selects.js');
            reload_js('/select/js/select2.js');
        },

        error: function (xhr) {
            console.log('erreur GET');
        }
    });
}

// Envoi des informations choisies sur le formulaire de partage de Macro
function receiveMacroForm(formData, macroId) {
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
            receiveShareData('success');
        },

        error: function (xhr) {
            receiveShareData('error');
        }
    });
};

// Gestion de l'affichage de modal suite à la réponse
function receiveShareData(status) {

    // Crée un message selon type de réponse
    if (status === 'success') {
        var textContent = $("<p></p>").text("La macro a bien été partageé.");
        var icon = $("<i class='fas fa-check'></i>");
    } else {
        var textContent = $("<p></p>").text("La macro n'a pas pu être partageé.");
        var icon = $("<i class='fas fa-exclamation-circle'></i>");
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
        messageContainer.append(textContent);
        messageContainer.append(icon);

        textContainer.fadeIn();
        textContainer.delay(1500).fadeOut();
        $('#share-container').empty();
    }
}

