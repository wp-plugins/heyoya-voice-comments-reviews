<?php
class PluginContainer{

	public function __construct(){
		add_filter('the_content', array($this, 'addHeyoyaToFooter'));		
	}

	function addHeyoyaToFooter($content){		
		if (is_feed() || is_home() || !is_singular() || !is_main_query())
			return $content;

		if (!is_heyoya_installed() || !was_heyoya_purchased())
			return $content;
		
		$options = get_option('heyoya_options', null);
		if ($options == null || !isset($options["affiliate_id"]) || !isset($options["is_store"]) || !isset($options["title"]) || !isset($options["title_color_text"]) || !isset($options["title_color_background"]) || !isset($options["placement_path"])){
			$heyoyaErrorAddition = "";
			if ($options != null && isset($options["affiliate_id"]))
					$heyoyaErrorAddition .= "&affiliateId=" . $options["affiliate_id"];

			$heyoyaErrorAddition .= "&pageUrl=" . "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			
			$content .= "<iframe style=\"width:1px;height:1px;position:absolute;top:-10000px:left:-10000px;\" src=\"https://commerce-static.heyoya.com/b2b/b2b_gr.jsp?action=wp-plugin-load-failed-missing-fields" . $heyoyaErrorAddition . "\"></iframe>";
			return $content;
		}
		
		$heyoyaScript = "<script type=\"text/javascript\">(function() {var heyoyaSettings = window.heyoyaSettings = {};heyoyaSettings.isStore = #IS_STORE#;heyoyaSettings.title = '#TITLE#';heyoyaSettings.titleColorText = '#TITLE_COLOR_TEXT#';heyoyaSettings.titleColorBackground = '#TITLE_COLOR_BACKGROUND#';heyoyaSettings.ppPath = '#PP_PATH#';heyoyaSettings.affId = '#AFFILIATE_ID#';var heyoya = document.createElement('script');heyoya.type = 'text/javascript';heyoya.async = true;heyoya.src = (window.navigator && window.navigator.userAgent && window.navigator.userAgent.toLowerCase().indexOf('windows nt 5.1') != -1)?'https://commerce.heyoya.com/b2b/b2b_loader.hey?affId=#AFFILIATE_ID#':'https://commerce-static.heyoya.com/b2b/b2b_loader.hey?affId=#AFFILIATE_ID#';var script = document.getElementsByTagName('script')[0];script.parentNode.insertBefore(heyoya, script);})();</script>";

		$heyoyaScript = str_replace("#IS_STORE#", $options["is_store"]?"true":"false", $heyoyaScript);
		$heyoyaScript = str_replace("#TITLE#", $options["title"], $heyoyaScript);
		$heyoyaScript = str_replace("#TITLE_COLOR_TEXT#", $options["title_color_text"], $heyoyaScript);
		$heyoyaScript = str_replace("#TITLE_COLOR_BACKGROUND#", $options["title_color_background"], $heyoyaScript);
		$heyoyaScript = str_replace("#PP_PATH#", $options["placement_path"], $heyoyaScript);
		$heyoyaScript = str_replace("#AFFILIATE_ID#", $options["affiliate_id"], $heyoyaScript);
		
		$content .= $heyoyaScript;	

		return $content; 
	}
}

?>
