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
    document.getElementById("file-name").innerHTML = displayText;
}, false);