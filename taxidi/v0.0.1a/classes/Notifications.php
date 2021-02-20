<?php
class Notifications {
    private $con;
    function __construct() {
        require_once dirname(__FILE__) . '/../../DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
	}
	
    public function isNotUserExist($username, $emailAddress) {
        $stmt = $this->con->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $emailAddress);
        $stmt->execute();
        $stmt->store_result();
        $result = $stmt->num_rows();
        $stmt->close();
        //error_log("isNotUserExist -> $result");
        return $result == 0;
    }
    
    public function getUserID($username){
        $stmt = $this->con->prepare("SELECT id FROM users WHERE username = ? AND user_closed = 0 AND user_banned = 0 LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($userID);
        $stmt->fetch();
        $stmt->close();
        return $userID;
    }
    
    public function getUsername($userID){
        $stmt = $this->con->prepare("SELECT username FROM users WHERE id = ? AND user_closed = 0 AND user_banned = 0 LIMIT 1");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
        return $username;
    }

    public function loadNotifications($userID, $last_id, $token){
        if ($token == null || $token == ""){
            return "1014";
        } else if ($last_id == null || $last_id == ""){
            return "1015";
        } else if ($userID == null || $userID == ""){
            return "1016";
        } else {
            if ($this->userOnline($userID, $token)) {
                $username = $this->getUsername($userID);
                if ($username != "" && $username != null) {
                    // add users.blocked_array?
                    $stmt = $this->con->prepare("SELECT	
                        notifications.id,
                        notifications.user_to,
                        notifications.user_from,
                        notifications.title,
                        notifications.message,
                        notifications.type,
                        notifications.link,
                        notifications.datetime,
                        notifications.opened,
                        notifications.viewed,
                        notifications.deleted,
                        users.id,
                        users.profile_pic,
                        users.display_name,
                        users.last_online
                    FROM
                        notifications
                    LEFT JOIN
                        users
                    ON
                        notifications.user_from = users.username
                    WHERE
                        notifications.user_to = ?
                    AND
                        users.user_banned = 'no'
                    AND
                        users.user_closed = 'no'
                    AND
                        notifications.id > ?
                    ORDER BY
                        notifications.id DESC"); 
        
                    $stmt->bind_param("si",$username, $last_id);
                    $stmt->execute();
                    $stmt->bind_result($id, $user_to, $user_from, $title, $message, $type, $link, $datetime, $opened, $viewed, $deleted, $user_id, $profile_pic, $display_name, $last_online);
                    $notifications = array(); 
                    while($stmt->fetch()){
                        /* $blocked = "";
                        $user_array_explode = explode(",", $blocked_array);
                        foreach ($user_array_explode as $i) {
                            if($i == $username && $i != "") {
                                $blocked = "yes";
                            }
                        }
                        if($blocked == "yes")continue; */
                        
                        $temp = array();
                        $temp['id'] = $id;
                        $temp['user_from'] = $user_from; 
                        $temp['title'] = $title; 
                        $temp['message'] = $message; 
                        $temp['type'] = $type; 
                        $temp['link'] = $link; 
                        $temp['datetime'] = $datetime; 
                        $temp['opened'] = $opened;
                        $temp['viewed'] = $viewed;
                        $temp['user_id'] = $user_id;
                        $temp['profile_pic'] = $profile_pic;
                        $temp['display_name'] = $display_name;
                        $temp['last_online'] = $last_online;
                        $temp['deleted'] = $deleted;
                        array_push($notifications, $temp);
                    }
                    $stmt->close();
                    return $notifications;
                } else {
                    return "1018";
                }
            } else {
                return "1017";
            }
        }
    }
		
    public function userOnline($userid, $token){
        $stmt = $this->con->prepare("SELECT id FROM user_auth_tokens WHERE `user_id` = ? AND `token` = ? LIMIT 1");
        $stmt->bind_param("is", $userid, $token);
        $stmt->execute(); 
        $stmt->store_result(); 
        if($stmt->num_rows > 0){
            $stmt->close();
            $date = date("Y-m-d H:i:s");
                if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                        $ip=$_SERVER['HTTP_CLIENT_IP'];
                    //Is it a proxy address
                    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
                    }else{
                        $ip=$_SERVER['REMOTE_ADDR'];
                    }
            $stmt = $this->con->prepare("UPDATE `users` SET `last_ip` = '$ip', `last_online` = '$date' WHERE `users`.`id` = ?");
            $stmt->bind_param("i", $userid);
            $stmt->execute(); 
            $stmt->store_result(); 
            $stmt->close();
            return true;
        } else {
            error_log("Notifications.userOnline error -> $userid, $token");
            return false;
        }
    }
    
	public function getNotificationTokens($usernames){
		$notiTokens = array();
		if(is_array($usernames)){
			$orMessage = "";
			for ($i = 0; $i < count($usernames); $i++)  {
				if ($i == 0){
					$orMessage .= "username='".$usernames[$i]."'";
				}else{
					$orMessage .= " OR username='".$usernames[$i]."'";
				}
			}
			$stmt = $this->con->prepare("SELECT token FROM user_fcm_tokens WHERE $orMessage AND active = 1");
			$stmt->execute();
			$stmt->bind_result($token);
			while($stmt->fetch()){
				if($token != ""){
					array_push($notiTokens, $token);
				}
			}
			$stmt->close();
			return $notiTokens;
		}else{
			$stmt = $this->con->prepare("SELECT token FROM user_fcm_tokens WHERE username = ? AND active = 1");
			$stmt->bind_param("s",$usernames);
			$stmt->execute();
			$stmt->bind_result($token);
			while($stmt->fetch()){
				if($token != ""){
					array_push($notiTokens, $token);
				}
			}
			$stmt->close();
			return $notiTokens;
		}
		return false;
	}
	
	public function pushNotiNew($notification, $notiTokens){
        $firebase_api = "AAAAHfS5vBE:APA91bGLE4VdewsKb5njZ_ko2aVm4HxwWIy7LYEgNVsqO2ps1QAawsKwG0NRsUCwtpyiQmVSUtR_KrwjxrhlC8hha_Y-XpewTrSIznFKNwZOaZiksoVOGmMSGtmrmEpme_BHwimwMEr1";
        $requestData = $notification->getNotification();
        $fields = array();
		$fields['data'] = $requestData;
		if(is_array($notiTokens)){
			if(count($notiTokens) > 1000){
				$firstNotiTokens = array_slice($notiTokens, 0, 1000);
				$secondNotiTokens = array_slice($notiTokens, 1000);
				$this->pushNotiNew($notification, $secondNotiTokens);
				$fields['registration_ids'] = $firstNotiTokens;
			}else{
				$fields['registration_ids'] = $notiTokens;
			}
		}else{
			$fields['to'] = $notiTokens;
		}
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=' . $firebase_api,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if($result === FALSE){
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
	}
    
	
}