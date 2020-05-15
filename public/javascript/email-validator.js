// Vérifie le format d'email sur les pages d'édition d'utilisateur

// Lors de l'envoi du formulaire d'édition de compte
var formSubmitButton = document.getElementsByClassName('edit-user')[0];
formSubmitButton.addEventListener('click', validateEmail);

function validateEmail() {
    var formErrors = document.getElementsByClassName('form-errors-container')[0];
    // Supprime le message d'erreur précedente (s'il y en a)
    if (formErrors.childNodes.length > 0) {
        while (formErrors.lastChild) {
            formErrors.removeChild(formErrors.lastChild);
        }
    }

    var form = document.getElementsByTagName('form')[0];
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    // Vérifie que l'email soit dans le format 'exemple@exemple.com'
    const emailField = document.getElementById('users_edit_email').value;
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    if (regex.test(emailField)) {
        // Envoi le formulaire si l'email est valide
        form.submit();
    } else {
        // Affiche un message d'erreur si l'email n'est pas valide
        var ul = document.createElement('ul');
        var li = document.createElement('li');
        li.innerText = emailField + ' n\'est pas un email valide';
        formErrors.appendChild(ul);
        ul.appendChild(li);
    }
}
