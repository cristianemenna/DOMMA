document.getElementById("import_file").addEventListener("change", function(){
    var fullPath = this.files;
    var displayText = 'Fichier.s. sélectionné.s. : ';

    for (var i = 0; i < fullPath.length; i++)
        if (i !== fullPath.length - 1) {
            displayText += fullPath[i].name + ", ";
        } else {
            displayText += fullPath[i].name;
        }

    document.getElementById("file-name").innerHTML = displayText;
}, false);