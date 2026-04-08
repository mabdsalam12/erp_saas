//var myPolygon;
function initialize() {
    var myLatLng = new google.maps.LatLng(centerLat,centerLng);
    // General Options
    var mapOptions = {
        zoom: zoomLavel,
        center: myLatLng,
        mapTypeId: google.maps.MapTypeId.RoadMap
    };
    var map = new google.maps.Map(document.getElementById('map'),mapOptions);
    // Polygon Coordinates
    var triangleCoords = [];
    for(i=0;i<oLats.length;i++){
        triangleCoords[i]=new google.maps.LatLng(oLats[i],oLngs[i]);
    }

    // Styling & Controls
    myPolygon = new google.maps.Polygon({
        paths: triangleCoords,
        draggable: false, // turn off if it gets annoying
        editable: true,
        strokeColor: '#FF0000',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#FF0000',
        fillOpacity: 0.35
    });

    myPolygon.setMap(map);
    //google.maps.event.addListener(myPolygon, "dragend", getPolygonCoords);
    google.maps.event.addListener(myPolygon.getPath(), "insert_at", getPolygonCoords);
    google.maps.event.addListener(myPolygon.getPath(), "remove_at", getPolygonCoords);
    google.maps.event.addListener(myPolygon.getPath(), "set_at", getPolygonCoords);
    var deleteNode = function(mev) {
        if (mev.vertex != null) {
            myPolygon.getPath().removeAt(mev.vertex);
        }
    }
    google.maps.event.addListener(myPolygon, 'rightclick', deleteNode);
}

//Display Coordinates below map
function getPolygonCoords() {
    var len = myPolygon.getPath().getLength();
    lats=[];
    lngs=[];
    for (var i = 0; i < len; i++) {
        var xy = myPolygon.getPath().getAt(i);
        lats[i]=xy.lat();
        lngs[i]=xy.lng();
    }
    $('#lats').val(lats.toString(','));
    $('#lngs').val(lngs.toString(','));
}
function copyToClipboard(text) {
    window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
}
/*$(document).ready(function(){
initialize();
});*/
google.maps.event.addDomListener(window, 'load', initialize);