loggedIn = function(){
	var $;
	function init(jqueryObj){
		$ = jqueryObj;		
		
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

				
				break;
				
			case "hey_purchased":
				requestData.action = "purchased";
				requestData.state = eventData.value == "1"?1:0;
				break;
				
			case "hey_is_store":
				requestData.action = "is_store";
				requestData.state = eventData.value == "1"?1:0;
				break;
				
			case "hey_design_mode":
				if (!eventData.value.title || !eventData.value.titleColorText || !eventData.value.titleColorBackground){
					requestData = null;
					break;
				}
				
				requestData.action = "design_mode";
				requestData.title = eventData.value.title;
				requestData.titleColorText = eventData.value.titleColorText;
				requestData.titleColorBackground = eventData.value.titleColorBackground;
				break;
				
			case "hey_placement_path":
				if (!eventData.value){
					requestData = null;
					break;
				}
				
				requestData.action = "placement_path";
				requestData.path = eventData.value;
				break;
		}
		
		
		
		
		
		if (requestData != null){
			$.post(ajaxurl, requestData, function(response) {
				if (requestData.action == "logout" && response == "1")
					window.location.reload(true); 
			});
		}		
	}
	
	function loadIframe(){
		var url = "http://admin.heyoya.com/client-admin/installation.heyoya?ak=" + $("#heyoyaContainer").attr("aa") + "&at=wp&v=1"; 
		
		$("#heyoyaContainer").append("<iframe style=\"width:" + ($("#wpcontent").width() - parseInt($("#wpcontent").css("padding-left"))) + "px;height:1200px;\" src=\"" + url + "\"></iframe>");		
	}
	
	
	
	return{
		init:init
	}

}();

jQuery(function(){
	loggedIn.init(jQuery);
});

