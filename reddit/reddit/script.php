<script src="js/jquery.js"></script>

<script>
	function vote(obj, type, num, val, pres)
	{
		var ajax = new XMLHttpRequest();
		
		if (val == "1")
		{
			if (pres == "hi")
			{
				obj.onmouseout = function() { obj.src = "img/uplo.png"; obj.style.cursor = "pointer" }
				val = "0";
			}
			
			else
			{
				obj.onmouseout = function() { obj.src = "img/uphi.png"; obj.style.cursor = "pointer" }
				var l = obj.parentNode.getElementsByTagName("img");
				for (var i = 0; i < l.length; ++i)
				{
					if (l[i].src.match(/^.*\/downhi.*$/i))
					{
						l[i].onmouseout = function() { l[i].src = "img/downlo.png"; l[i].style.cursor = "pointer" }
						l[i].onmouseout();
					}
				}
			}
		}
		
		else
		{
			if (pres == "hi")
			{
				obj.onmouseout = function() { obj.src = "img/downlo.png"; obj.style.cursor = "pointer" }
				val = "0"
			}
			
			else
			{
				obj.onmouseout = function() { obj.src = "img/downhi.png"; obj.style.cursor = "pointer" }
				var l = obj.parentNode.getElementsByTagName("img");
				for (var i = 0; i < l.length; ++i)
				{
					if (l[i].src.match(/^.*\/uphi.*$/i))
					{
						l[i].onmouseout = function() { l[i].src = "img/uplo.png"; l[i].style.cursor = "pointer" }
						l[i].onmouseout();
					}
				}
			}
		}
		
		ajax.open("POST", "?", true);
		ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajax.send("type="+type+"&num="+num+"&val="+val);
	}
</script>
