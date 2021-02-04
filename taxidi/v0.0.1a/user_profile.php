<?php
require_once 'classes/UserOperations.php';
//require_once 'classes/User.php';
//require_once '../libs/gcm/gcm.php';
require 'libs/Slim-2.x/Slim/Slim.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();
 
// User id from db - Global Variable
$user_id = NULL;

// Get Profile ID
$app->get('/get_profile_by_username', function () use ($app){
    
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
            $response['error'] = false;
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
        $response['error'] = false;
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