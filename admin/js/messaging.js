window.heyoyaMessaging = (function(){
    var getMessageCallbackFunction;

    function init(clb){
        getMessageCallbackFunction = clb;
        if( window.addEventListener ){
            window.addEventListener("message", this.gotMessage, false );
        }else{
            window.attachEvent('onmessage', this.gotMessage );
        }
    }


    function postMessage(targetWindow, message ){
        targetWindow.postMessage( message, "*" );
    }

    function gotMessage(event){
    	if (!event || !event.data || typeof event.data.indexOf != "function" || event.data.indexOf("_heymsg_") != 0 || !getMessageCallbackFunction || typeof getMessageCallbackFunction != "function")
    		return;   	
    	
    	var jsonObject = null;
    	
    	try {
    		jsonObject = JSON.parse(event.data.substring(8));
    	} catch (err){
    		jsonObject = null;    		
    	}
    	
    	if ( jsonObject == null || !jsonObject.action || !jsonObject.value )
    		return;    	
    	
    	
        getMessageCallbackFunction(jsonObject);
        
    }

    function dispose(){
        getMessageCallbackFunction = undefined;

        if( window.removeEventListener ){
            window.removeEventListener("message", this.gotMessage, false );
        }else{
            if (window.detachEvent) {
                window.detachEvent ('onmessage', this.gotMessage );
            }
        }
    }


    return {
        init: init,
        dispose: dispose,
        postMessage: postMessage,
        gotMessage:gotMessage
    };
}());