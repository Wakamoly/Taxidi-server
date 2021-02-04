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

    public function loadProfile($userID) {

        $stmt = $con->prepare("SELECT username FROM users WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $userID);
        $stmt->execute(); 
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
        
        $stmt = $con->prepare("SELECT
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
                users.status
            FROM
                users
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
                $status
            );       
            $stmt->fetch();
            $stmt->close();

            $ratingnumber = 5;
    
            $ratingNumber2 = 0;
            $count = 0;
            $fiveStarRatings = 1;
            $fourStarRatings = 0;
            $threeStarRatings = 0;
            $twoStarRatings = 0;
            $oneStarRatings = 0;
    
            $ratingNumber2+=$ratingnumber;
            $count += 1;
        
            $average = 0;
            if($ratingNumber2 && $count) {
                $average = $ratingNumber2/$count;
            }
            
            $averagefinal = round($average,2);
            
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
            $profile['average'] = $averagefinal;
            
            return $profile;
        } else {
            $stmterr = $stmt->error;
            error_log("UserOp.loadProfile($userID) -> ".$stmterr);
            return false;
        }
        
    }
    
	
    
	
}