function ClearNeighborhoodControl() {
    var mainDiv = document.createElement('div');
    mainDiv.style.padding = '5px';

    var controlUI = document.createElement('div');
    controlUI.style.backgroundColor = 'white';
    controlUI.style.borderStyle = 'solid';
    controlUI.style.borderWidth = '2px';
    controlUI.style.cursor = 'pointer';
    controlUI.style.textAlign = 'center';
    controlUI.title = 'Clickear para limpiar el filtro de  barrios';
    mainDiv.appendChild(controlUI);

    var controlText = document.createElement('div');
    controlText.style.fontFamily = 'Arial,sans-serif';
    controlText.style.fontSize = '12px';
    controlText.style.paddingLeft = '4px';
    controlText.style.paddingRight = '4px';
    controlText.innerHTML = '<b>Limpiar filtro barrial</b>';
    controlUI.appendChild(controlText);

    var clickListener = null;

    this.addToMap = function(map, clickHandler) {
        this.removeFromMap(map);
        var controlsArray = map.controls[google.maps.ControlPosition.TOP_RIGHT];
        controlsArray.push(mainDiv);
        var context = this;
        clickListener = google.maps.event.addDomListener(mainDiv, 'click', function() {
            clickHandler(context);
        });
    };

    this.removeFromMap = function(map) {
        var auxControlsArray = map.controls[google.maps.ControlPosition.TOP_RIGHT];
        var controlIndex = auxControlsArray.indexOf(mainDiv);
        if (controlIndex !== -1) {
            google.maps.event.removeListener(clickListener);
            auxControlsArray.removeAt(controlIndex);
        }
    };
}