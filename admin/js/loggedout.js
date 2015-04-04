loggedOut = function(){
	var $;
	var regEmail = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	function init(jqueryObj){
		$ = jqueryObj;
		
		bindEvents();
	}
	
	function bindEvents(){
	
		$("#heyoyaSignUpDiv form").on("submit", function(){			
			return validateFields($("#heyoyaSignUpDiv"));
		});
		
		$("#heyoyaLoginDiv form").on("submit", function(){			
			return validateFields($("#heyoyaLoginDiv"));
		});
		
		$("#heyoyaLoginDiv .alternate a").on("click", function(){
			$("#heyoyaAdmin .updated span").addClass("invisible");
			$("#heyoyaAdmin .updated").addClass("invisible");
			
			$("#heyoyaLoginDiv").addClass("invisible");
			$("#heyoyaSignUpDiv").removeClass("invisible");
		});
		
		$("#heyoyaSignUpDiv .alternate a").on("click", function(){
			$("#heyoyaAdmin .updated span").addClass("invisible");
			$("#heyoyaAdmin .updated").addClass("invisible");
			$("#heyoyaSignUpDiv").addClass("invisible");
			$("#heyoyaLoginDiv").removeClass("invisible");
			
			
		});


	}
	
	function validateFields(baseObj){		
		if (!baseObj)
			return false;
			
		baseObj.find(".updated").addClass("invisible");
		baseObj.find(".updated span").addClass("invisible");

		var validated = true;			

		var emailIsMissing = false;
		var emailInput = baseObj.find(".login_email");
		if (emailInput.val() == ""){
			validated = false;
			emailIsMissing = true;
			baseObj.find(".email_missing").removeClass("invisible");
		}
		
		if (!emailIsMissing && !regEmail.test(emailInput.val())){			
			validated = false;
			baseObj.find(".email_invalid").removeClass("invisible");	
		}

		var passwordInput = baseObj.find(".login_password");
		if (passwordInput.val() == ""){
			validated = false;
			baseObj.find(".password_missing").removeClass("invisible");	
		}

		
		var nameInput = baseObj.find(".signup_fullname");
		if (nameInput.length != 0 && nameInput.val() == ""){
			validated = false;
			baseObj.find(".name_missing").removeClass("invisible");	
		}
		
		if (!validated)
			baseObj.find(".updated").removeClass("invisible");
		
		return validated;

	}
	
	return{
		init:init
	}
}();



jQuery(function(){
	loggedOut.init(jQuery);
});