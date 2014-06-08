var ENDPOINT = 'http://localhost/mdp-map/index.php/';
var map;
var mapOptions = {
	zoom: 12,
	center: new google.maps.LatLng(-38, -57.55),
	mapTypeId: google.maps.MapTypeId.TERRAIN
};

var zones =[]; //array for map zones polygons

var neighArray = [];
var callArray = [];

function displayNeighborhoods(neighborhoods) {
	var lastNeigh = "";
	var groupedNeighborhoodsByZone = _.groupBy(neighborhoods, 'zone_id');
	for(var zone_id in groupedNeighborhoodsByZone) {
		(function(){
			zones[zone_id] = [];
			var neighs = groupedNeighborhoodsByZone[zone_id];
			var groupedNeighborhoodsByName = _.groupBy(neighs, 'name');
			for(var name in groupedNeighborhoodsByName)
				(function() {
					var neighborhood = groupedNeighborhoodsByName[name];
					var coordinates = _.map(neighborhood, function(x) { return new google.maps.LatLng(x.lat, x.lon); });
					var neighborhoodPolygon = new google.maps.Polygon({
						paths: coordinates,
						strokeColor: '#000000',
						strokeOpacity: 0.8,
						strokeWeight: 3,
						fillColor: '#' + (0x1000000 + Math.random() * 0xFFFFFF).toString(16).substr(1,6),
						fillOpacity: 0.35
					});
					neighborhoodPolygon.setMap(map);
					neighArray.push(neighborhoodPolygon);
					zones[zone_id].push(neighborhoodPolygon);
				})();
			}
		)();
	}
}

$(function () {
	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	$.get('index.php/neighborhoods', function(response) {
		displayNeighborhoods(JSON.parse(response));
	});
	$('#calls_button').click(function() {
		
		return false;
	});
});
