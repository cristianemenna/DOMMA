document.getElementById("import_file").addEventListener("change", function(){
    var fullPath = this.files;
    var displayText = 'Fichier.s. sélectionné.s. : ';

    // Itère sur le tableau de fichiers chargés par l'utilisateur
    for (var i = 0; i < fullPath.length; i++)
        if (i !== fullPath.length - 1) {
            displayText += fullPath[i].name + ", ";
        } else {
            // Si c'est le dernier element, n'ajoute pas de virgule à la fin
            displayText += fullPath[i].name;
        }

    // Ajoute le texte sur l'html pour affichage du nom des fichiers chargés
    document.getElementById("file-messages").innerHTML = displayText;
}, false);

// Affiche un message si l'utilisateur click sur "Envoyer" sans avoir choisit un fichier
document.getElementById("send-file").addEventListener("click", function(){
    var sentFiles = document.getElementById("import_file").files;
    if (sentFiles.length === 0) {
        document.getElementById("file-messages").innerHTML = "Veuillez choisir un fichier.";
    }
});

// Supprime un message d'erreur lors que l'utilisateur click sur "Choisir un fichier"
document.getElementById("import").addEventListener("click", function(){
    var elementMessageParent = document.getElementsByClassName("file-errors-container")[0];
    if (elementMessageParent.lastChild.previousSibling !== document.getElementById("file-messages")) {
        elementMessageParent.lastChild.remove();
    }

});
