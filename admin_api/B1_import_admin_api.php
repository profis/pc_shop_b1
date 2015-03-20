<?php

class B1_import_admin_api extends PC_plugin_admin_api {

	protected $_rest;
	
	protected function _set_plugin_name() {
		$this->_plugin_name = 'b1';
	}
	
	protected function _rest($what, $id=null, $lid=null, $mdate=null, $addparams=array()) {
		if (is_null($this->_rest)) {
			$this->_rest = new B1_rest($this->cfg['b1']['private_key'], $this->cfg['b1']['api_key']);
		}
		return $this->_rest->rest($what, $id, $lid, $mdate, $addparams);
	}
	
	public function test() {
		if (is_null($this->site->default_ln)) {
			$this->site->Identify();	
		}
		echo 'testas';
		echo "ln: {$this->site->ln} ";
		echo "default ln: {$this->site->default_ln} ";
	}
	
	
	public function katalogas($id=null, $lid=null, $mdate=null) {
		if (is_null($this->site->default_ln)) {
			$this->site->Identify();	
		}
		
		$this->shop = $this->core->Get_object('PC_shop_manager');
		$category = $this->_get_import_category();
		$this->_out['count_created'] = 0;
		$this->_out['count_existing'] = 0;
		if (!$category) {
			$this->_out['error'] = true;
			$this->_out['error_message'] = 'Import category not found. Please create a category with externa_id = import';
			return;
		}
		//print_pre($category);
		$last_list_item = false;
		$more = false;
		do {
			$more = true;
			if ($last_list_item) {
				$lid = $last_list_item->id;
			}
			$last_list_item = false;
			$list = $this->_rest('eshopkatalogas', $id, $lid, $mdate, array());
			if ($list) {
				$ref_ids = array();
				foreach ($list as $key => $list_item) {
					//print_pre($list_item);
					$ref_ids[] = $list_item->id;
				}
				$existing_products = $this->shop->products->get_all(array(
					'where' => array(
						'external_id' => $ref_ids,
					),
					'key' => 'external_id'
				));
				//print_pre($existing_products);
				$iteration_count = 0;
				foreach ($list as $key => $list_item) {
					$last_list_item = $list_item;
					if (isset($existing_products[$list_item->id])) {
						$this->_out['count_existing']++;
						continue;
					}
					$create_params = array();
					$data = array(
						'external_id' => $list_item->id,
						'quantity' => intval($list_item->likutis),
						'mpn' => $list_item->kodas,
						'is_not_quantitive' => $list_item->preke?0:1,
						'contents' => array(
							$this->site->ln => array(
								'name' => $list_item->pavadinimas
							)
						)
					);
					if ($data['is_not_quantitive'] and $data['quantity'] == 0) {
						$data['quantity'] = 1;
					}
					//print_pre($data);
					$created = $this->shop->products->create($category['id'], 0, $data, $create_params);
					if ($created) {
						$this->_out['count_created']++;
					}
					else {
						//print_pre($create_params);
					}
					//break;

				}
				
			}
			if (!$last_list_item) {
				$more = false;
			}
		} while ($more);
		$this->_out['success'] = true;
	}

	protected function _get_import_category() {
		$category = false;
		if (isset($this->cfg['b1']) and isset($this->cfg['b1']['import_category'])) {
			$category_id = trim($this->cfg['b1']['import_category']);
			$category = $this->shop->categories->get_one($category_id);
		}
		if (!$category) {
			$category = $this->shop->categories->get_one(array(
				'where' => array(
					'external_id' => 'import'
				)
			));
		}
		return $category;
	}

	/*
	prekiu nomenklaturos gavimo pvz
	*/
	public function katalogas_test($id=null, $lid=null, $mdate=null) {			
		$list = $this->_rest('eshopkatalogas', $id, $lid, $mdate, array());
		if ($list) {
			echo '<pre>'.print_r($list, true).'</pre>';
		}
	}	
	
	public function likuciai_test($id=null, $lid=null, $mdate=null) {	
		$list = $this->_rest('eshoplikuciai', $id, $lid, $mdate, array());
		if ($list) {
			echo '<pre>'.print_r($list, true).'</pre>';
		}
	}	
	
	public function likuciai($id=null, $lid=null, $mdate=null) {	
		$this->shop = $this->core->Get_object('PC_shop_manager');
		$last_list_item = false;
		$this->_out['count_existing'] = 0;
		$this->_out['count_updated'] = 0;
		$this->_out['count_total'] = 0;
		$more = false;
		do {
			$more = true;
			if ($last_list_item) {
				$lid = $last_list_item->id;
			}
			$last_list_item = false;
			$list = $this->_rest('eshoplikuciai', $id, $lid, $mdate, array());
			if ($list) {
				$ref_ids = array();
				foreach ($list as $key => $list_item) {
					$last_list_item = $list_item;
					$ref_ids[] = $list_item->id;
				}
				$existing_products = $this->shop->products->get_all(array(
					'where' => array(
						'external_id' => $ref_ids,
					),
					'key' => 'external_id'
				));
				//print_pre($existing_products);
				foreach ($list as $key => $list_item) {
					$this->_out['count_total']++;
					if (isset($existing_products[$list_item->id])) {
						$this->_out['count_existing']++;
						$quantity = $list_item->likutis;
						if ($existing_products[$list_item->id]['is_not_quantitive']) {
							$quantity = 1;
						}
						$rez = $this->shop->products->update(array(
							'quantity' => $quantity
						), array(
							'where' => array(
								'external_id' => $list_item->id
							),
							'limit' => 1
						));
						if ($rez) {
							$this->_out['count_updated']++;
						}
					}
				}

			}
			if (!$last_list_item) {
				$more = false;
			}
		} while ($more);
		
		$this->_out['success'] = true;
	}	
	
	public function pardavimas_test() {
		$order = array(
			'prefix'=>'shop', //kazkoks parduotuve idetinkuojantis prefiksas iki 10 simboliu
			'orderid'=>4, //uzsakymo id eshop'o sistemoje
			'orderdate'=>date('Y-m-d'), //pardavimo data
			'orderno'=>'442', //uzsakymo numeris eshop'o sistemoje			
			'currency'=>'EUR', //valiutos kodas pagal ISO
			'discount'=>10.1*100, //nuolaidu suma * 100
			'total'=>10.1*100, //uzsakymo suma *100
			'orderemail'=>'info@vilkas.lt',
			'billing'=>array(
				'refid'=>null, //kliento id B1 sistemoje jei zinomas
				'name'=>'Vardenis Pavardenis', 
				'iscompany'=>false,
				'code'=>'',
				'vatcode'=>'',
				'address'=>'Pievu g 16',
				'city'=>'Klaipeda',
				'postcode'=>'8213',
				'country'=>'Lietuva',
			),
			'delivery'=>array(
				'name'=>'Vardenis Pavardenis',
				'iscompany'=>false,
				'code'=>'',
				'vatcode'=>'',
				'address'=>'Pievu g 16',
				'city'=>'Klaipeda',
				'postcode'=>'8213',
				'country'=>'Lietuva',				
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
				0=>array('id'=>'', 'name'=>'Kažkoks šlamštas', 'quantity'=>1.1*100, 'price'=>66.99*100, 'sum'=>73.69*100),
				1=>array('id'=>14, 'name'=>'B1 preke su id', 'quantity'=>1*100, 'price'=>15.0*100, 'sum'=>60.3*100),
			)
		);
		//0=>array('id'=>'', 'name'=>'supper puper preke', 'quantity'=>1*100, 'price'=>110.05*100, 'sum'=>110.05*100),
		//1=>array('id'=>'', 'name'=>'supper duper preke', 'quantity'=>3*100, 'price'=>10.55*100, 'sum'=>31.65*100),
		//2=>array('id'=>'', 'name'=>'supper duper paslauga', 'quantity'=>6*100, 'price'=>10.55*100, 'sum'=>63.3*100),
		
		$list = $this->_rest('pardavimas', null, null, null, $order);
		if ($list) {
			echo '<pre>'.print_r($list, true).'</pre>';
		}
	}
	
}

?>
