heyoyaReport = function(){
	var sessionId, pageUrl;
	
	function init(){
		sessionId = _createUUID();
		pageUrl = encodeURIComponent(window.location.href);
	}
	
	function report(action, sync){		
		if (sync)
			_doReport(action);
		else
			setTimeout(function(){ _doReport(action); }, 0);
	}
	
	function _doReport(action){
		if (!action)
			return;

		action = encodeURIComponent(action);
		
		var report = document.createElement("iframe");
		report.style = "width:1px;height:1px;position:absolute;top:-10000px:left:-10000px";
		report.frameBorder = 0;
		report.src = "https://commerce-static.heyoya.com/b2b/b2b_gr.jsp?action=" + action + "&pageUrl=" + pageUrl + "&sessionId=" + sessionId;
		
		document.getElementsByTagName("body")[0].appendChild(report);	
	}
	
	function _createUUID(){
        var s = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx';
        var n = s.replace(/[xy]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
        return n;
	}
	
	return{
		init: init,
		report: report
	}
}();
