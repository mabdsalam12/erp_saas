var drawingManager;
var selectedShape;
function clearSelection () {
    if (selectedShape) {
        selectedShape.setEditable(false);
        selectedShape = null;
    }
}

function setSelection (shape) {
    clearSelection();
    lats=[];
    lngs=[];

    // getting shape coordinates
    var v = shape.getPath();
    for (var i=0; i < v.getLength(); i++) {
        var xy = v.getAt(i);
        lats[i]=xy.lat();
        lngs[i]=xy.lng();
    }
    $('#lats').val(lats.toString(','));
    $('#lngs').val(lngs.toString(','));
    

    selectedShape = shape;
    shape.setEditable(true);
}

function deleteSelectedShape () {
    if (selectedShape) {
        selectedShape.setMap(null);
    }
}
function markerAreaSet(p){
    $('#markerLat').val(p.lat());
    $('#markerLng').val(p.lng());
}

function initialize () {
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: zoomLavel,
        center: new google.maps.LatLng(centerLat,centerLng),
        disableDefaultUI: true,
        zoomControl: true
    });
    var polyOptions = {
        strokeWeight: 1,
        fillOpacity: 0.6,
        editable: true,
        draggable: false
    };
    // Creates a drawing manager attached to the map that allows the user to draw
    // markers, lines, and shapes.
    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_LEFT,
            drawingModes: [
                //google.maps.drawing.OverlayType.MARKER,
                google.maps.drawing.OverlayType.POLYGON
            ]
        },
        markerOptions: {
            draggable: true
        },
        polylineOptions: {
            editable: true,
            draggable: false
        },
        rectangleOptions: polyOptions,
        circleOptions: polyOptions,
        polygonOptions: polyOptions,
        map: map
    });

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function (e) {

        if (e.type == google.maps.drawing.OverlayType.MARKER) {
            markerAreaSet(e.overlay.position);
        }
        else{
            // Switch back to non-drawing mode after drawing a shape.
            drawingManager.setDrawingMode(null);
            // Add an event listener that selects the newly-drawn shape when the user
            // mouses down on it.
            var newShape = e.overlay;
            newShape.type = e.type;
            google.maps.event.addListener(newShape, 'click', function (e) {
                if (e.vertex !== undefined) {
                    if (newShape.type === google.maps.drawing.OverlayType.POLYGON) {
                        var path = newShape.getPaths().getAt(e.path);
                        path.removeAt(e.vertex);
                        if (path.length < 3) {
                            newShape.setMap(null);
                        }
                    }
                    if (newShape.type === google.maps.drawing.OverlayType.POLYLINE) {
                        var path = newShape.getPath();
                        path.removeAt(e.vertex);
                        if (path.length < 2) {
                            newShape.setMap(null);
                        }
                    }
                }

                setSelection(newShape);
            });
            setSelection(newShape);
        }

    });
    
    google.maps.event.addListener(drawingManager, 'markercomplete'  , function(e) {
        console.log(e);
        setTimeout(function() {
            e.setMap(null);
            }, 5000);

    });
    google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
    google.maps.event.addListener(map, 'click', clearSelection);
    google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
}
google.maps.event.addDomListener(window, 'load', initialize);