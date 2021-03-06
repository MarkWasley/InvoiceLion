<?php 
$projects = DB::select('select `id`,`name`,`customer_id`,`default_hourly_fee` from `projects` WHERE `tenant_id` = ? and `active` ORDER BY name', $_SESSION['user']['tenant_id']);
$customers = DB::selectPairs('select `id`,`name` from `customers` WHERE `tenant_id` = ? ORDER BY name', $_SESSION['user']['tenant_id']);
$hourtypes = DB::selectPairs('select `id`,`name` from `hourtypes` WHERE `tenant_id` = ?', $_SESSION['user']['tenant_id']);
$languageId = DB::selectValue('select `language_id` from `tenants`, `countries` WHERE `tenants`.`id` = ? AND `tenants`.`country_id` = `countries`.`id`', $_SESSION['user']['tenant_id']);
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$data = $_POST;

	if ($data['hours']['add_customer']) {
		$data['hours']['customer_id'] = DB::insert('INSERT INTO `customers` (`tenant_id`, `name`, `language_id`) VALUES (?, ?, ?)', $_SESSION['user']['tenant_id'], $data['hours']['add_customer'], $languageId);
		$data['hours']['add_customer'] = null;
		$customers = DB::selectPairs('select `id`,`name` from `customers`  WHERE `tenant_id` = ?', $_SESSION['user']['tenant_id']);
	}
	
	//set tax_percentage to NULL if the customer has tax_reverse_charge
	$tax_reverse_charge = DB::selectValue('select `tax_reverse_charge` from `customers` WHERE `tenant_id` = ? AND `id` = ?', $_SESSION['user']['tenant_id'], $data['hours']['customer_id']);
	if ($tax_reverse_charge) $data['hours']['tax_percentage']=NULL;
	
	if (!$data['hours']['project_id']) $data['hours']['project_id']=NULL;
	if (!$data['hours']['comment']) $data['hours']['comment']=NULL;
	if (!$data['hours']['type']) $data['hours']['type']=NULL;
	if (!$data['hours']['date']) $errors['hours[date]']='Date not set';	
	if (!$data['hours']['hours_worked']) $errors['hours[hours_worked]']='Hours worked not set';	
	if (!$data['hours']['hourly_fee']) $data['hours']['hourly_fee']=NULL;
	if (!$data['hours']['tax_percentage'] && !$tax_reverse_charge) $errors['hours[tax_percentage]']='tax percentage not set';	
	if (!$data['hours']['customer_id']) $errors['hours[customer_id]']='Customer not set';	
	
	if (!isset($errors)) {
		try {
			$subtotal = $data['hours']['hours_worked'] * $data['hours']['hourly_fee'];

			$hour_id = DB::insert('INSERT INTO `hours` (`tenant_id`, `customer_id`, `project_id`, `date`, `name`, `hours_worked`, `hourly_fee`, `subtotal`, `tax_percentage`, `type`, `comment`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['user']['tenant_id'], $data['hours']['customer_id'], $data['hours']['project_id'], $data['hours']['date'], $data['hours']['name'], $data['hours']['hours_worked'], $data['hours']['hourly_fee'], $subtotal, $data['hours']['tax_percentage'], $data['hours']['type'], $data['hours']['comment']);

			if ($hour_id) {
 				Flash::set('success','Hours saved');
 				Router::redirect('hours/index');
			}
			$error = 'Hours not saved';
		} catch (DBError $e) {
			$error = 'Hours not saved: '.$e->getMessage();
		}
	}	
} else {
	$data = array('hours'=>array(
		'customer_id'=>NULL, 
		'add_customer'=>NULL, 
		'project_id'=>NULL, 
		'date'=>date("Y-m-d"), 
		'name'=>NULL, 
		'hours_worked'=>NULL, 
		'hourly_fee'=>$tenant['tenants']['default_hourly_fee'], 
		'subtotal'=>NULL, 
		'tax_percentage'=>$tenant['tenants']['default_tax_percentage'], 
		'type'=>NULL, 
		'comment'=>NULL,
		'invoiceline_id'=>NULL));
}