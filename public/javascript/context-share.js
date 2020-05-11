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
    });

    $('#icon-share-context-modal').click(function () {
        $('#share-context-modal-message').empty();
        $('#share-context-overlay').toggle();
        $('#share-context-modal').toggle();
    });

    $('#share-context-form').submit(function(e) {
        e.preventDefault();
        formData = $('#share_context_users').val();
        // console.log(formData);
        sendShareContext(formData, $('#context-id').val());
    })

});

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
            $('#share-context-container').append(decoded);
            $('#share-context-modal-message').empty();
            $('#share-context-modal-message').toggle();
            $('#share-context-overlay').toggle();
            $('#share-context-modal').toggle();

            // Permet d'actualiser l'information des fichiers .js pour bien retrouver les nouveaux éléments
            function reload_js(src) {
                $('script[src="' + src + '"]').remove();
                $('<script>').attr('src', src).appendTo('head');
            }
            reload_js('/javascript/context-share.js');
            reload_js('/javascript/selects.js');
            reload_js('/select/js/select2.js');

        },

        error: function (xhr) {
        }
    });
}

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
            // Ajout d'un message de confirmation
            var textContent = $("<p></p>").text("Le contexte a bien été modifié.");

            $('#share-context-modal').toggle(500);
            $('#share-context-modal-message').append(textContent);
            $('#share-context-modal-message').append($("<i class='fas fa-check'></i>"));
            $('#share-context-modal-message').toggle();
            $('#share-context-overlay').delay(1500).fadeOut();
        },
        error: function (xhr) {
            // Ajout d'un message d'erreur
            var textContent = $("<p></p>").text("Le contexte n'a pas pu être modifié.");

            $('#share-context-modal').toggle(500);
            $('#share-context-modal-message').append(textContent);
            $('#share-context-modal-message').append($("<i class='fas fa-exclamation-circle'></i>"));
            $('#share-context-modal-message').toggle();
            $('#share-context-overlay').delay(1500).fadeOut();
        }
    });
};
