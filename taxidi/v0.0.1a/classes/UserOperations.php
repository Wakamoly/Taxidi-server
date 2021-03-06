<?php
class UserOperations {
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

    public function loadProfile($userID) {
        $stmt = $this->con->prepare("SELECT username FROM users WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $userID);
        $stmt->execute(); 
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
        
        $stmt = $this->con->prepare("SELECT
                users.display_name,
                users.description,
                users.type,
                users.profile_pic,
                users.back_pic,
                users.user_closed,
                users.user_banned,
                users.verified,
                users.last_online,
                users.num_shipped,
                users.status,
                user_info.num_reviews,
                user_info.average_rating
            FROM
                users
            LEFT JOIN
                user_info
            ON
                users.id = user_info.user_id
            WHERE
                users.username = ?
            LIMIT 1"); 
        
        $stmt->bind_param("s",$username);
        if($stmt->execute()) {
            $stmt->bind_result(
                $display_name,
                $description,
                $type,
                $profile_pic,
                $back_pic,
                $user_closed,
                $user_banned,
                $verified,
                $last_online,
                $num_shipped,
                $status,
                $num_reviews,
                $average_rating
            );       
            $stmt->fetch();
            $stmt->close();
            
            $profile['display_name'] = $display_name; 
            $profile['description'] = $description;
            $profile['type'] = $type;
            $profile['profile_pic'] = $profile_pic;
            $profile['back_pic'] = $back_pic;
            $profile['user_closed'] = $user_closed;
            $profile['user_banned'] = $user_banned;
            $profile['verified'] = $verified;
            $profile['last_online'] = $last_online;
            $profile['num_shipped'] = $num_shipped;
            $profile['status'] = $status;
            $profile['average'] = $average_rating;
            $profile['review_count'] = $num_reviews;
            
            return $profile;
        } else {
            $stmterr = $stmt->error;
            error_log("UserOp.loadProfile($userID) -> ".$stmterr);
            return false;
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
        } else return false;
    }
    
	
    
	
}