;(function(window, document, $, dog, undefined) {

	/***** DOM Ready *****/

	$(function() {

	});

	/***** Various Functions *****/

	function isRoute(controller, action = 'index') {
		return isPage(`req-${controller}-${action}`);
	}

	function isPage(css_class) {
		if (css_class.indexOf('.') !== 0) {
			css_class = '.' + css_class;
		}
		return $('body').is(css_class);
	}

  function inArray(needle, haystack) {
  	return $.inArray(needle, haystack) !== -1;
  }

	function isDevice(device) {
		return $('.device-' + device).is(':visible');
	}

})(window, document, jQuery, _dog_obj);