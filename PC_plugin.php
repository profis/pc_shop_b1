<?php


function b1_after_order_create($params) {
	global $cfg;
	$rest = new B1_rest($cfg['b1']['private_key'], $cfg['b1']['api_key']);
	
	$order = array(
		'prefix'=>$cfg['b1']['shop_id'], //kazkoks parduotuve idetinkuojantis prefiksas iki 10 simboliu
		'orderid'=>$params['order_id'], //uzsakymo id eshop'o sistemoje
		'orderdate'=>date('Y-m-d', $params['order_data']['date']), //pardavimo data
		'orderno'=>$params['order_id'], //uzsakymo numeris eshop'o sistemoje			
		'currency'=>$params['order_data']['currency'], //valiutos kodas pagal ISO
		'discount'=>$params['order_data']['discount']*100, //nuolaidu suma * 100
		'total'=>$params['order_data']['total_price']*100, //uzsakymo suma *100
		'orderemail'=>$params['order_data']['email'],
		'billing'=>array(
			'refid'=>null, //kliento id B1 sistemoje jei zinomas
			'name'=>$params['order_data']['name'], 
			'iscompany'=>v($params['other_data']['is_company'], false),
			'code'=>v($params['other_data']['billing_code'], ''),
			'vatcode'=>v($params['other_data']['billing_vatcode'], ''),
			'address'=>$params['order_data']['address'],
			'city'=>v($params['other_data']['city'], ''),
			'postcode'=>v($params['other_data']['postcode'], ''),
			'country'=>v($params['other_data']['country'], ''),
		),
		'delivery'=>array(
			'name'=>$params['order_data']['name'],
			'iscompany'=>v($params['other_data']['is_company'], false),
			'code'=>v($params['other_data']['delivery_code'], ''),
			'vatcode'=>v($params['other_data']['delivery_vatcode'], ''),
			'address'=>$params['order_data']['address'],
			'city'=>v($params['other_data']['city'], ''),
			'postcode'=>v($params['other_data']['postcode'], ''),
			'country'=>v($params['other_data']['country'], ''),		
		),			
		'items'=>array( //parduotos pozcijos 
			/*
			0=> array(
				'id'=>'', //prekes id B1 sistemoje jei žinomas
				'name'=>'Kažkoks šlamštas', //prekes pavadinimas eshop'o sistemoje
				'quantity'=>1.1*100, //parduotas kiekis*100
				'price'=>66.99*100,  //pardavimo kaina*100
				'sum'=>73.69*100	//suma*100
			)
			*/
			//0=>array('id'=>'', 'name'=>'Kažkoks šlamštas', 'quantity'=>1.1*100, 'price'=>66.99*100, 'sum'=>73.69*100),
			//1=>array('id'=>14, 'name'=>'B1 preke su id', 'quantity'=>1*100, 'price'=>15.0*100, 'sum'=>60.3*100),
		)
	);
	
	foreach ($params['order_data']['items'] as $item) {
		$order['items'][] = array(
			'id'=>$item['external_id'], 
			'name'=>$item['name'],
			'quantity'=>$item['quantity'], 
			'price'=>$item['price']*100, 
			'sum'=>$item['quantity']*$item['price']*100
		);
	}
	
	//0=>array('id'=>'', 'name'=>'supper puper preke', 'quantity'=>1*100, 'price'=>110.05*100, 'sum'=>110.05*100),
	//1=>array('id'=>'', 'name'=>'supper duper preke', 'quantity'=>3*100, 'price'=>10.55*100, 'sum'=>31.65*100),
	//2=>array('id'=>'', 'name'=>'supper duper paslauga', 'quantity'=>6*100, 'price'=>10.55*100, 'sum'=>63.3*100),

	//$test_items = $rest->rest('eshopkatalogas');

	$list = $rest->rest('pardavimas', null, null, null, $order);
}


$core->Register_hook('plugin/pc_shop/after-order-create', 'b1_after_order_create');

$thisPath =  dirname(__FILE__) . '/';
$clsPath = $thisPath . 'classes/';
Register_class_autoloader('B1_rest', $clsPath.'B1_rest.php');
