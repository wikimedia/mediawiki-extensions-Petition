$(document).ready(function() {
	// If site has geolocation, automatically select country
	if( typeof(window.Geo) !== 'undefined' ) {
		$('.petition-form #mw-input-wpcountry').val(Geo.country);
	}
});