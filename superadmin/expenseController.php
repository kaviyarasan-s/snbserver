<?php
	include_once '../model/db.php';
	$con=db_connect();
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)){
		$_POST = json_decode(file_get_contents('php://input'), true);
		$_REQUEST = $_POST;
	}

	$function = $_REQUEST['flag'];
	unset($_REQUEST['flag']);

	if (!empty($function)) {
		switch ($function) {

			case 'getExpenseDetails':
				getExpenseDetails($_REQUEST,$con);
				break;

			case 'addExpenseDetails':
				addExpenseDetails($_REQUEST,$con);
				break;

			case 'updateExpenseDetails':
				updateExpenseDetails($_REQUEST,$con);
				break;

			case 'deleteExpenseDetails':
				deleteExpenseDetails($_REQUEST,$con);
				break;

			default:
				$response = array('status' => 'failed','message' => 'Something went wrong.');
				echo json_encode($response);
				break;
		}
	}else{
		$response = array('status' => 'failed','message' => 'Something went wrong.');
		echo json_encode($response);
	}

	function getBusDetailsDB($conn){
		$data = select('`bus_no`, `driver_beta_percent`, `conductor_beta_percent`, `beta_start_limit_one`, `beta_end_limit_one`, `beta_amount_one`, `beta_start_limit_two`, `beta_end_limit_two`, `beta_amount_two`, `auto_inc_id`','bus_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				
			}
		}
		return $data == "empty"?[]: $data;
	}

	function getExpenseDetailsDB($conn){
		$data = select('*','expense_details','1',$conn);
		if ($data != "empty") {
			foreach ($data as $key => $value) {
				$data[$key]['view_date'] = date("d-m-Y", strtotime($value['entry_date']));
				$data[$key]["other"] = unserialize($value["other"]);
			}
		}
		return $data == "empty"?[]: $data;
	}

	function getExpenseDetails($request,$conn){
		$response = array(
			'status' => 'success',
			'data' => getExpenseDetailsDB($conn),
			'busData' => getBusDetailsDB($conn)
		);
		echo json_encode($response);
	}

	function addExpenseDetails($request,$conn){
		$request['entry_date'] = date('Y-m-d', strtotime($request['entry_date']));
		$request['created_date'] = date('Y-m-d');
		$request['modified_date'] = date('Y-m-d');
		$request["other"] = serialize($request["other"]);

		$data = select('*','expense_details','entry_date = "'.$request['entry_date'].'"',$conn);
		if ($data != "empty") {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "One entry allowed per date",
				'data' => getExpenseDetailsDB($conn),
				'busData' => getBusDetailsDB($conn)
			);
		}else{
			if(insert('expense_details',$request,$conn)){
				$response = array(
					'status' => '200',
					'response' => 'success',
					'message' => "Details added Successfully",
					'data' => getExpenseDetailsDB($conn),
					'busData' => getBusDetailsDB($conn)
				);
			}else {
				$response = array(
					'status' => '401',
					'response' => 'error',
					'message' => "Failed to add Details",
					'data' => getExpenseDetailsDB($conn),
					'busData' => getBusDetailsDB($conn)
				);
			}
		}
		echo json_encode($response, JSON_PRETTY_PRINT);	
	}

	function updateExpenseDetails($request,$conn){
		$condition = 'id="'.$request['id'].'"';
		$request['entry_date'] = date('Y-m-d', strtotime($request['entry_date']));
		$request['modified_date'] = date('Y-m-d');
		$request["other"] = serialize($request["other"]);

		$update = update($request,'expense_details',$condition,$conn);
		if($update){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Details updated Successfully",
				'data' => getExpenseDetailsDB($conn),
				'busData' => getBusDetailsDB($conn)

			);
		}else {
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to update details",
				'data' => getExpenseDetailsDB($conn),
				'busData' => getBusDetailsDB($conn)

			);
		}
		echo json_encode($response, JSON_PRETTY_PRINT);			
	}

	function deleteExpenseDetails($request,$conn){
		$condition="`id` = '".$request['id']."'";
		$delete = delete('expense_details',$condition,$conn);

		if($delete){
			$response = array(
				'status' => '200',
				'response' => 'success',
				'message' => "Expense details deleted Successfully",
				'data' => getExpenseDetailsDB($conn),
				'busData' => getBusDetailsDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);
		}else{
			$response = array(
				'status' => '401',
				'response' => 'error',
				'message' => "Failed to delete Expense Details",
				'data' => getExpenseDetailsDB($conn),
				'busData' => getBusDetailsDB($conn)
			);
			echo json_encode($response, JSON_PRETTY_PRINT);	 
		}		
	}

