var map;
var mapOptions = {
	zoom: 12,
	center: new google.maps.LatLng(-38, -57.55),
	mapTypeId: google.maps.MapTypeId.TERRAIN
};
function initialize() {
	map = new google.maps.Map(document.getElementById('map-canvas', mapOptions));
	var zones =[]; //array for map zones polygons
	var zoneArray1 = []; //arrays for neighborhoods in zones
	var zoneArray2 = [];
	var zoneArray3 = [];
	var zoneArray4 = [];
	var zoneArray5 = [];
	var neighArray = [];
	var callArray = [];

}

google.maps.event.addDomListener(window, 'load', initialize);
