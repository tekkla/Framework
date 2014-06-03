function fileChange()
{
    //FileList Objekt aus dem Input Element mit der ID "fileA"
    var fileList = document.getElementById("fileA").files;

    //File Objekt (erstes Element der FileList)
    var file = fileList[0];

    //File Objekt nicht vorhanden = keine Datei ausgewählt oder vom Browser nicht unterstützt
    if(!file)
        return;

    document.getElementById("fileName").innerHTML = 'Dateiname: ' + file.name;
    document.getElementById("fileSize").innerHTML = 'Dateigröße: ' + file.size + ' B';
    document.getElementById("fileType").innerHTML = 'Dateitype: ' + file.type;
    document.getElementById("progress").value = 0;
    document.getElementById("prozent").innerHTML = "0%";
}

var client = null;

function uploadFile()
{
    //Wieder unser File Objekt
    var file = document.getElementById("fileA").files[0];
    //FormData Objekt erzeugen
    var formData = new FormData();
    //XMLHttpRequest Objekt erzeugen
       client = new XMLHttpRequest();

    var prog = document.getElementById("progress");

    if(!file)
        return;

    prog.value = 0;
    prog.max = 100;

    //Fügt dem formData Objekt unser File Objekt hinzu
    formData.append("datei", file);

    client.onerror = function(e) {
        alert("onError");
    };

    client.onload = function(e) {
        document.getElementById("prozent").innerHTML = "100%";
        prog.value = prog.max;
    };

    client.upload.onprogress = function(e) {
        var p = Math.round(100 / e.total * e.loaded);
        document.getElementById("progress").value = p;
        document.getElementById("prozent").innerHTML = p + "%";
    };

    client.onabort = function(e) {
        alert("Upload abgebrochen");
    };

    client.open("POST", "upload.php");
    client.send(formData);
}

function uploadAbort() {
    if(client instanceof XMLHttpRequest)
        client.abort();
}