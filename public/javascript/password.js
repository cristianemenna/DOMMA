var submitButton = document.getElementById('submit-button');
submitButton.addEventListener('click', validatePassword);

// Valide si les deux champs du nouveau de passe correspondent
function validatePassword() {
    var form = document.getElementsByTagName('form')[0];
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    var formErrors = document.getElementsByClassName('form-errors-container')[0];
    // Supprime le message d'erreur précedente (s'il y en a)
    if (formErrors.childNodes.length > 0) {
        while (formErrors.lastChild) {
            formErrors.removeChild(formErrors.lastChild);
        }
    }

    var firstField = document.getElementById('users_password_password').value;
    var secondField = document.getElementById('password-confirmation').value;

    if (firstField !== secondField) {
        // Affiche un message d'erreur si l'email n'est pas valide
        var ul = document.createElement('ul');
        var li = document.createElement('li');
        li.innerText = 'Les deux champs ne correspondent pas.';
        formErrors.appendChild(ul);
        ul.appendChild(li);
    } else {
        form.submit();
    }
}

