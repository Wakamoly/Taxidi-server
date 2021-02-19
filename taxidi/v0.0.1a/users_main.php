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

$app->post('/upload_fcm_token', function () use ($app){

    if ($token != null && $token != "null") {
        if(!$db->createFCMRow($username, $user_id, $token, "")){
            error_log("users_main.php/login.createFCMRow error -> db->createFCMRow($username, $user_id, $token, blank)");
        }
    }

});

// Login to Taxidi
$app->get('/login', function () use ($app){
    
    verifyRequiredParams(array('email', 'password'));
    
    $password = $app->request()->get('password');
    $emailAddress = $app->request()->get('email');
    
    // Removing this functionality for better practice of generating a key, then sending to the user's device
    //$headers = getallheaders();
    //$token = $headers['Authorization'];
    
    $response = array();

    $db = new DbOperations();
    if($db->isUserExist($emailAddress)){
        $result = $db->userLogin($emailAddress, $password);

        if($result != false){
			$response['error'] = false;
            $response['code'] = "0002";
            $username = $result["username"];
            $user_id = $result["user_id"];
            $result['auth_token'] = $db->generateAuthToken($username, $user_id);
            error_log("Login token -> " . $result['auth_token']);
            $response['result'] = $result;
            
		}else{
			$response['error'] = true;
            $response['code'] = "1010";
            $response['result'] = null;
        }

    } else {
        $response['error'] = true; 
        $response['code'] = "1009";
        $response['result'] = null;
    }
    echoResponse(200,$response);
});
 
//This will store the FCM token to the database
$app->post('/storefcmtoken', function () use ($app) {
    verifyRequiredParams(array('fcm_token', 'username', 'user_id', 'old_token'));

    $username = $app->request()->post('username');
    $user_id = $app->request()->post('user_id');
    $fcmtoken = $app->request()->post('fcm_token');
    $old_token = $app->request()->post('old_token');

    $headers = getallheaders();
    $auth_token = $headers['Authorization'];

    $db = new DbOperations();

    $response = array();
    $result = $db->createFCMRow($username, $user_id, $fcmtoken, $old_token, $auth_token);

    // Generic response
    if ($result < 1000){
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    $response['code'] = str_pad((string)$result, 4, '0', STR_PAD_LEFT);

    echoResponse(200, $response);
});
 
// This will get all home view stuff for drivers
$app->post('/gethomeinfo', function () use ($app) {
    verifyRequiredParams(array('username', 'user_id', 'last_news_id', 'last_log_id'));

    $username = $app->request()->post('username');
    $user_id = $app->request()->post('user_id');
    $last_news_id = $app->request()->post('last_news_id');
    $last_log_id = $app->request()->post('last_log_id');

    $headers = getallheaders();
    $auth_token = $headers['Authorization'];

    $response = array();
    $db = new DbOperations();
    if($db->userOnline($user_id, $auth_token)){
        $top_result = $db->userHomeTopDetails($user_id, $username);
        $log_result = $db->homeLogDetails($user_id, $username, $last_log_id);
        $news_result = $db->homeNewsDetails($user_id, $username, $last_news_id);

        if($top_result != false && $log_result != false && $news_result != false){
			$response['error'] = false;
            $response['code'] = "0002";
            $response['top_result'] = $top_result;
            // list
            $response['log_result'] = $log_result;
            // list
            $response['news_result'] = $news_result;
		} else {
			$response['error'] = true;
            $response['code'] = "1020";
            $response['top_result'] = null;
            $response['log_result'] = null;
            $response['news_result'] = null;
        }

    } else {
        $response['error'] = true; 
        $response['code'] = "1017";
        $response['top_result'] = null;
        $response['log_result'] = null;
        $response['news_result'] = null;
    }

    echoResponse(200, $response);
});
 
//This will remove the FCM token from the database
$app->post('/removefcmtoken', function () use ($app) {
    verifyRequiredParams(array('token', 'username', 'user_id'));

    $username = $app->request()->post('username');
    $user_id = $app->request()->post('user_id');
    $token = $app->request()->post('token');
    
    $db = new DbOperations();

    $response = array();
    if ($db->removeFCMToken($username, $user_id, $token)) {
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoResponse(200, $response);
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