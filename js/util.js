var getUriParams = function() {
    var searchString = document.location.search;
    searchString = searchString.substring(1);

    var nvPairs = searchString.split("&");
    var params = {};
    for (var i = 0; i < nvPairs.length; i++)
    {
        var nvPair = nvPairs[i].split("=");
        params[nvPair[0]] = nvPair[1];
    }
    return params;
}

Math.easeInOutCubic = function (t, b, c, d) {
	t /= d/2;
	if (t < 1) return c/2*t*t*t + b;
	t -= 2;
	return c/2*(t*t*t + 2) + b;
};
Math.easeOutCubic = function (t, b, c, d) {
	t /= d;
	t--;
	return c*(t*t*t + 1) + b;
};
Math.easeOutQuad = function (t, b, c, d) {
	t /= d;
	return -c * t*(t-2) + b;
};
var fadeOut = function (id) {
    var color = [255, 255, 0].join(',') + ',',
        element = document.getElementById(id),
        interval = 50,
        t = interval,
        timeout = setInterval(function(){
            if(t >= 0){
                element.style.backgroundColor = 'rgba(' + color + Math.easeOutQuad(t-=1, 0, 1, interval) + ')'; // (transparency -= 0.015)
                // (1 / 0.015) / 25 = 2.66 seconds to complete animation
            } else {
                clearInterval(timeout);
            }
        }, interval); // 1000/40 = 25 fps
}