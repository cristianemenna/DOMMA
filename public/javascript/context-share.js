$(document).ready(function () {

    $('#share-context').click(function(e) {
        e.preventDefault();
        if ($('#context-id').val()) {
            getTemplate($('#context-id').val());
        }
    });

    // Toggle du modal lors d'un clic sur bouton "annuler" ou icon de fermeture
    $('#cancel-share-context').click(function () {
        $('#share-context-modal-message').empty();
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
        $('#share-context-container').empty();
    });

    $('#icon-share-context-modal').click(function () {
        $('#share-context-modal-message').empty();
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
        $('#share-context-container').empty();
    });

    $('#share-form').submit(function(e) {
        e.preventDefault();
        formData = $('#share_context_users').val();
        sendShareContext(formData, $('#context-id').val());
    })

});

// Récupère le template avec formulaire de partage de Context
function getTemplate(contextId) {
    $.ajax({
        type: 'GET',
        url: '/contextes/' + contextId + '/share',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
            // Template twig en réponse
            decoded = JSON.parse(data);

            if ($('#share-context-container').html().length === 0 ) {
                $('.overlay-message').remove();
                $('#share-context-container').append(decoded);
                $('#share-context-modal-message').empty();
                $('#share-context-modal-message').toggle();
                $('#share-context-overlay').toggle();
                $('#share-context-modal').toggle();
            }

            // Permet d'actualiser l'information des fichiers .js pour bien retrouver les nouveaux éléments
            reloadJs('/javascript/context-share.js');
            reloadJs('/javascript/selects.js');
            reloadJs('/select/js/select2.js');
        },

        error: function (xhr) {
            console.log('erreur GET');
        }
    });
}

// Envoi des informations choisies sur le formulaire de partage de Context
function sendShareContext(formData, contextId) {
    $.ajax({
        type: 'POST',
        url: '/contextes/' + contextId + '/share',
        data: JSON.stringify(formData),
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        async: true,

        success: function (data) {
            reloadJs('/javascript/context-share.js');
            reloadJs('/javascript/selects.js');
            reloadJs('/select/js/select2.js');
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
        var textContent = $("<p></p>").text("Le contexte a bien été modifié.");
    } else {
        var textContent = $("<p></p>").text("Le contexte n'a pas pu être modifié.");
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
        $('#share-context-container').empty();

        // Toggle sur affichage de l'option de partage sur show Context
        if ($('.to-share-context').length) {
            var newDiv = $("<div></div>").addClass('icon-shared-context');
            var newI = $("<i></i>").addClass('fas fa-user-friends share-context');
            var newP = $("<p></p>").text("Contexte partagé");
            newDiv.append(newI);
            newDiv.attr('id', 'share-context');
            $('.to-share-context')[0].remove();
        } else {
            var newDiv = $("<div></div>").addClass('to-share-context');
            newDiv.css('cursor', 'pointer');
            var newP = $('<p></p>').text('Partager');
            newP.attr('id', 'share-context');
            $('.icon-shared-context')[0].remove();
        }

        var newContentContainer = $('#title-context-container');
        newDiv.append(newP);
        newContentContainer.append(newDiv);

    }
}

// Permet d'actualiser l'information des fichiers .js pour bien retrouver les nouveaux éléments
function reloadJs(src) {
    $('script[src="' + src + '"]').remove();
    $('<script>').attr('src', src).appendTo('head');
}

