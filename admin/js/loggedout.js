heyoyaLoggedOut = function(){
	var $, heyoyaReportService;	
	var regEmail = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	
	function init(jqueryObj, heyoyaReportObj){
		$ = jqueryObj;
		heyoyaReportService = heyoyaReportObj;
		bindEvents();

		
		initialReport();
		
		if (heyoyaErrorCode != undefined && heyoyaErrorCode != 0){
			heyoyaReportService.report("wp-error-" + heyoyaErrorCode);
			heyoyaErrorCode = 0;
		}
	}
	
	function bindEvents(){
	
		$("#heyoyaSignUpDiv form").on("submit", function(){
			$(this).find("input[type=submit]").val("Please wait...");			
			heyoyaReportService.report("wp-signup-form-submit");
			
			if (validateFields($("#heyoyaSignUpDiv"))){								
				heyoyaReportService.report("wp-signup-form-submit-validation-success", true);
				return true;
			} else {
				$(this).find("input[type=submit]").val($(this).find("input[type=submit]").attr("original_value"));
				heyoyaReportService.report("wp-signup-form-submit-validation-failure");				
				return false;
			}			
		});
		
		$("#heyoyaLoginDiv form").on("submit", function(){
			$(this).find("input[type=submit]").val("Please wait...");		
			heyoyaReportService.report("wp-login-form-submit");
			
			if (validateFields($("#heyoyaLoginDiv"))){
				heyoyaReportService.report("wp-login-form-submit-validation-success", true);
				return true;
			} else {
				$(this).find("input[type=submit]").val($(this).find("input[type=submit]").attr("original_value"));
				heyoyaReportService.report("wp-login-form-submit-validation-failure");
				return false;
			}						 
		});
		
		$("#heyoyaLoginDiv .alternate a").on("click", function(){
			$("#heyoyaAdmin .updated span").addClass("invisible");
			$("#heyoyaAdmin .updated").addClass("invisible");
			
			$("#heyoyaLoginDiv").addClass("invisible");
			$("#heyoyaSignUpDiv").removeClass("invisible");
			
			heyoyaReportService.report("wp-change-mode-login2signup");
		});
		
		$("#heyoyaSignUpDiv .alternate a").on("click", function(){
			$("#heyoyaAdmin .updated span").addClass("invisible");
			$("#heyoyaAdmin .updated").addClass("invisible");
			$("#heyoyaSignUpDiv").addClass("invisible");
			$("#heyoyaLoginDiv").removeClass("invisible");
			
			heyoyaReportService.report("wp-change-mode-signup2login");
		});


	}
	
	function initialReport(){
		if ($("#heyoyaLoginDiv").hasClass("invisible"))
			heyoyaReportService.report("wp-signup-impression");
		else
			heyoyaReportService.report("wp-login-impression");		
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
	heyoyaReport.init(jQuery);
	heyoyaLoggedOut.init(jQuery, heyoyaReport);
});