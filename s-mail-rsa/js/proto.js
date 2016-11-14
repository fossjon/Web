String.prototype.formDate = function() {
	var a = new Date(parseInt(this) * 1000);
	
	var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	var month = months[a.getMonth()];
	
	var date = a.getDate();
	if (date < 10) { date = ("0" + date); }
	
	var hour = a.getHours();
	var desig = "AM";
	if (11 < hour) { desig = "PM"; }
	hour = (hour % 12);
	if (hour == 0) { hour = 12; }
	if (hour < 10) { hour = ("0" + hour); }
	
	var mins = a.getMinutes();
	if (mins < 10) { mins = ("0" + mins); }
	
	return (month + " " + date + ", " + hour + ":" + mins + " " + desig);
}

String.prototype.hexTOstr = function() {
	var o = "", l = this.length;
	for (var x = 0; x < l; x += 2)
	{
		o += String.fromCharCode(parseInt(this.substr(x, 2), 16));
	}
	return o;
}

String.prototype.rstrTrim = function() {
	var s = this, l = this.length;
	while ((l > 0) && (s.charCodeAt(l - 1) < 32))
	{
		s = s.substring(0, l - 1); l -= 1;
	}
	return s;
}
