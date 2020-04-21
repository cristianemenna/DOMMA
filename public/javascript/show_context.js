document.getElementById("import_file").addEventListener("change", function(){
    var fullPath = this.value;
    var fileName = fullPath.split(/(\\|\/)/g).pop();
    document.getElementById("file-name").innerHTML = 'Fichier sélectionné : ' + fileName;
}, false);