const submitButton = document.getElementById('submit-button');
submitButton.addEventListener('click', validatePassword);

// Valide si les deux champs du nouveau de passe correspondent
function validatePassword() {
    const form = document.getElementsByTagName('form')[0];
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    const firstField = document.getElementById('users_password_password').value;
    const secondField = document.getElementById('password-confirmation').value;

    if (firstField !== secondField) {
        alert('Les deux champs ne correspondent pas.');
    } else {
        form.submit();
    }
}
