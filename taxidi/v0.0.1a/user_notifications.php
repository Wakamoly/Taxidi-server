<?php
require_once 'classes/Notifications.php';
require 'libs/Slim-2.x/Slim/Slim.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();

// Load user notifications
$app->get('/load_notifications', function () use ($app){
    
    verifyRequiredParams(array('userID', 'last_id'));
    
    $userID = $app->request()->get('userID');
    $last_id = $app->request()->get('last_id');

    $headers = getallheaders();
    $token = $headers['Authorization'];

    // Stupid shit quit working unless I put the "www." before sabotcommunity.com in the Constants file. Ugh.
    //error_log("load_notifications -> token: $token");
    
    $response = array();

    $db = new Notifications();
    $result = $db->loadNotifications($userID, $last_id, $token);

    if (is_array($result)){
        $response['error'] = false;
        $response['code'] = "0004";
        $response['result'] = $result;
    } else {
        $response['error'] = true;
        $response['code'] = "$result";
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