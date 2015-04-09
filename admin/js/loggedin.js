heyoyaLoggedIn = function(){
	var $, heyoyaReportService;
	function init(jqueryObj, heyoyaReportObj){
		$ = jqueryObj;		
		heyoyaReportService = heyoyaReportObj;
		
		initMessaging();
		loadIframe();
	}
	
	function initMessaging(){
		if (!window.heyoyaMessaging)
			return;
		
		window.heyoyaMessaging.init(messagingCallback);
	}
	
	function messagingCallback(eventData){
		if (!eventData || !eventData.action || !eventData.value)
			return;

		var requestData = {};		
		switch (eventData.action){
			case "hey_logout":
				requestData.action = "logout";
				
				var oImg = document.createElement("img");
		        oImg.setAttribute('src', "http://admin.heyoya.com/client-admin/login/logout.heyoya?&r1=" + Math.random() + "&r2=" + Math.random() );
		        oImg.setAttribute('width', '1px');
		        oImg.setAttribute('height', '1px');
		        document.body.appendChild(oImg);

				heyoyaReportService.report("wp-admin-logout", true);		        		        
		        
				break;
				
			case "hey_purchased":
				requestData.action = "purchased";
				requestData.state = eventData.value == "1"?1:0;
				
				heyoyaReportService.report("wp-admin-purchased");
				break;
				
			case "hey_is_store":
				requestData.action = "is_store";
				requestData.state = eventData.value == "1"?1:0;
				
				heyoyaReportService.report("wp-admin-set-store");
				break;
				
			case "hey_design_mode":
				if (!eventData.value.title || !eventData.value.titleColorText || !eventData.value.titleColorBackground){
					requestData = null;
					break;
				}
				
				requestData.action = "design_mode";
				requestData.title = eventData.value.title;
				requestData.title_color_text = eventData.value.titleColorText;
				requestData.title_color_background = eventData.value.titleColorBackground;
				
				heyoyaReportService.report("wp-admin-set-design-mode");
				break;
				
			case "hey_placement_path":
				if (!eventData.value){
					requestData = null;
					break;
				}
				
				requestData.action = "placement_path";
				requestData.path = eventData.value;
				
				heyoyaReportService.report("wp-admin-set-placement-path");
				break;
		}
		
		
		
		
		
		if (requestData != null){
			$.post(ajaxurl, requestData, function(response) {
				if ($.trim(response) == "1"){
					
					heyoyaReportService.report("wp-admin-" + requestData.action + "-success", requestData.action == "logout");
				} else 
					heyoyaReportService.report("wp-admin-" + requestData.action + "-error");
				
				if (requestData.action == "logout" && $.trim(response) == "1")
					window.location.reload(true); 
			});
		}		
	}
	
	function loadIframe(){
		var pageUrl = "installation";
		if (heyoyaIsP != undefined && heyoyaIsP == 1)
			pageUrl = "home";
			
		
		var url = "http://admin.heyoya.com/client-admin/" + pageUrl + ".heyoya?ak=" + $("#heyoyaContainer").attr("aa") + "&at=wp&v=1"; 
		
		$("#heyoyaContainer").append("<iframe style=\"width:" + ($("#wpcontent").width() - parseInt($("#wpcontent").css("padding-left"))) + "px;height:1200px;\" src=\"" + url + "\"></iframe>");
		
		heyoyaReportService.report("wp-loggedin-impression");
	}
	
	
	
	return{
		init:init
	}

}();

jQuery(function(){
	heyoyaReport.init(jQuery);
	heyoyaLoggedIn.init(jQuery, heyoyaReport);
});
