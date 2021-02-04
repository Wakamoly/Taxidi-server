<?php
require_once 'classes/DbOperations.php';
//require_once 'classes/User.php';
//require_once '../libs/gcm/gcm.php';
require 'libs/Slim-2.x/Slim/Slim.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();
 
// User id from db - Global Variable
$user_id = NULL;
 
// Register to Taxidi
 $app->post('/register', function () use ($app){

    //Verifying required parameters
    verifyRequiredParams(array(
        'signInAs',
        'username',
        'emailAddress', 
        'password',
        'authorityType', 
        'type',
        'companyName', 
        'streetAddress', 
        'city',
        'state', 
        'zipCode',
        'country', 
        'companyPhone', 
        'firstName',
        'lastName', 
        'personalPhone'
    ));
 
    //Getting request parameters
    $signInAs = $app->request()->post('signInAs');
    $username = $app->request()->post('username');
    $emailAddress = $app->request()->post('emailAddress');
    $password = $app->request()->post('password');
    $authorityType = $app->request()->post('authorityType');
    $type = $app->request()->post('type');
    $companyName = $app->request()->post('companyName');
    $streetAddress = $app->request()->post('streetAddress');
    $city = $app->request()->post('city');
    $state = $app->request()->post('state');
    $zipCode = $app->request()->post('zipCode');
    $country = $app->request()->post('country');
    $companyPhone = $app->request()->post('companyPhone');
    $firstName = $app->request()->post('firstName');
    $lastName = $app->request()->post('lastName');
    $personalPhone = $app->request()->post('personalPhone');
    
    $response = array();

    $db = new DbOperations();
    if($db->isNotUserExist($username, $emailAddress)){
        $result = $db->createUser(
            $signInAs,
            $username,
            $emailAddress,
            $password,
            $authorityType,
            $type,
            $companyName,
            $streetAddress,
            $city,
            $state,
            $zipCode,
            $country,
            $companyPhone,
            $firstName,
            $lastName,
            $personalPhone
        );

        if($result == 1){
			$response['error'] = false;
            $response['code'] = "0001";
		}elseif($result == 2){
			$response['error'] = true; 
			$response['code'] = "1001";
		}elseif($result == 3){
			$response['error'] = true; 
			$response['code'] = "1002";
		}elseif($result == 4){
			$response['error'] = true; 
			$response['code'] = "1003";
		}elseif($result == 5){
			$response['error'] = true; 
			$response['code'] = "1004";
		}elseif($result == 0){
			$response['error'] = true; 
			$response['code'] = "1005";			
		}elseif($result == 6){
			$response['error'] = true; 
			$response['code'] = "1006";		
		}elseif($result == 7){
			$response['error'] = true; 
			$response['code'] = "1007";
		}

    } else {
        $response['error'] = true; 
        $response['code'] = "1008";
    }
    echoResponse(200,$response);
});

// Get Profile ID
$app->get('/get_user_id', function () use ($app){
    
    verifyRequiredParams(array('username'));
    
    $username = $app->request()->get('username');

    $headers = getallheaders();
    $token = $headers['Authorization'];
    
    $response = array();

    $db = new UserOperations();
    $result = $db->getUserID($username);

    if($result != false){
        $result = $db->loadProfile($result);
        if($result != false) {
            $response['error'] = true;
            $response['code'] = "0003";
            $response['result'] = $result;
        } else {
            $response['error'] = true;
            $response['code'] = "1012";
            $response['result'] = null;
        }
        
    }else{
        $response['error'] = true;
        $response['code'] = "1011";
        $response['result'] = null;
    }
    echoResponse(200,$response);
});

// Load profile main bits
$app->get('/load_profile', function () use ($app){
    
    verifyRequiredParams(array('userID'));
    
    $userID = $app->request()->get('userID');

    $headers = getallheaders();
    $token = $headers['Authorization'];
    
    $response = array();

    $db = new UserOperations();
    $result = $db->loadProfile($userID);

    if($result != false) {
        $response['error'] = true;
        $response['code'] = "0003";
        $response['result'] = $result;
    } else {
        $response['error'] = true;
        $response['code'] = "1012";
        $response['result'] = null;
    }
    echoResponse(200,$response);
});
 
 
//Function to display the response in browser
function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
    // setting response content type to json
    $app->contentType('application/json');
    echo json_encode($response);
}
 
 
//Function to verify required parameters
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}
 
 
 
function authenticate(\Slim\Route $route)
{
    //Implement authentication if needed 
}
 
 
$app->run();