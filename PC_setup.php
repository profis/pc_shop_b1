<?php
function b1_install($controller) {
	global $core;
	
	$core->Set_config_if('api_key', '', $controller);
	$core->Set_config_if('private_key', '', $controller);
	$core->Set_config_if('shop_id', 'b1_shop', $controller);
	
	return true;
}

function pc_shop_payment_paypal_uninstall($controller) {
	global $core;
	
	//Todo: delete config
	
	return true;
}