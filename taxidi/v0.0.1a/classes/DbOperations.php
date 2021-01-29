<?php 
	class DbOperations{
		private $con; 
		function __construct(){
			require_once dirname(__FILE__).'/../../DbConnect.php';
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
			return $result = 0; 
		}
		
		/*public function updateUser($username, $nickname, $email, $bio, $twitch, $mixer, $psn, $xbox, $steam, $youtube, $instagram, $clantag, $discord_server){
		    
		    $stmt2 = $this->con->prepare("SELECT username, email FROM users WHERE username=? LIMIT 1");
			$stmt2->bind_param("s",$username);
			$stmt2->execute();
			$stmt2->bind_result($matched_user, $user_email);
			$stmt2->fetch();
			$stmt2->close();
        
        	if($matched_user == $username) {
                if (strpos($email, '@') !== false) {
                    if(strlen($nickname) <= 3) {
						return 7;
					}else{
                	    $stmt = $this->con->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
            			$stmt->bind_param("s", $email);
            			$stmt->execute(); 
            			$stmt->store_result(); 
            			if($stmt->num_rows == 0||$user_email==$email){
            			    $stmt->close();
            			    
                    		$bio = strip_tags($bio); //removes html tags
            	        	
            	        	$email = strip_tags($email); //removes html tags
            	        	$email = str_replace('\r\n', "", $email);
            	        	
            	        	//error_log($discord." ".$discordpos);
                            
                            if (strpos($mixer, 'mixer.com') !== false&&($mixerpos = strrpos($mixer, "/")) !== FALSE) { 
                                $mixer = substr($mixer, $mixerpos+1); 
                            }
                            if ((strpos($twitch, 'twitch.tv') !== false || strpos($twitch, 'twitch.com') !== false)&&($twitchpos = strrpos($twitch, "/")) !== FALSE) { 
                                $twitch = substr($twitch, $twitchpos+1); 
                            }
                            if ((strpos($youtube, 'youtube.com') !== false||strpos($youtube, 'youtube.com') !== false)&&($youtubepos = strrpos($youtube, "/")) !== FALSE) { 
                                $youtube = substr($youtube, $youtubepos+1); 
                            }
                            if (strpos($discord_server, 'discord.gg') !== false&&($discordserverpos = strrpos($discord_server, "/")) !== FALSE) { 
                                $discord_server = substr($discord_server, $discordserverpos+1); 
                            }
                            if (strpos($instagram, 'instagram.com') !== false&&($instagrampos = strrpos($instagram, "/")) !== FALSE) { 
                                $instagram = substr($instagram, $instagrampos+1); 
                            }
            	        	//error_log($discord." ".$discordpos);
            	        	
            	        	$twitch = str_replace('\r\n', "%20", $twitch);
            	        	$mixer = str_replace('\r\n', "%20", $mixer);
            	        	$psn = str_replace('\r\n', "%20", $psn);
            	        	$xbox = str_replace('\r\n', "%20", $xbox);
            	        	$steam = str_replace('\r\n', "%20", $steam);
            	        	$instagram = str_replace('\r\n', "%20", $instagram);
            	        	$youtube = str_replace('\r\n', "%20", $youtube);
            	        	$discord_server = str_replace('\r\n', "%20", $discord_server);
            	        	
            	        	$stmt = $this->con->prepare("UPDATE users SET nickname=?, email=?, description=?, twitch=?, mixer=?, psn=?, xbox=?, steam=?, clan_tag=?, youtube=?, instagram=?, discord=? WHERE username=?");
                			$stmt->bind_param("sssssssssssss", $nickname,$email,$bio,$twitch,$mixer,$psn,$xbox,$steam,$clantag,$youtube,$instagram,$discord_server,$username);
                			$stmt->execute();
                    		return 1;
                    	}else{
                    	    return 2;
                    	}
					}
            	}else{
            	    return 4;
            	}
        	}else{
        		return 6;
        	}
		}*/
		
		/* public function updateUserPass($username, $new_password, $old_password_1, $old_password_2){
                
        	$new_password = strip_tags($new_password);
        	$old_password_1 = strip_tags($old_password_1);
        	$old_password_2 = strip_tags($old_password_2);
            
            $stmt2 = $this->con->prepare("SELECT password FROM users WHERE username=?");
            $stmt2->bind_param("s",$username);
            $stmt2->execute();
            $stmt2->bind_result($db_password);
            $stmt2->fetch();
            $stmt2->close();
            
            if(password_verify($old_password_2, $db_password)){
    				if($old_password_1 == $old_password_2) {
    					if(strlen($new_password) <= 4) {
    						return 2;
    					}else{
    						$new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    						$stmt = $this->con->prepare("UPDATE users SET password=? WHERE username=?");
                			$stmt->bind_param("ss",$new_password_hash,$username); //was $password
                			$stmt->execute();
                            $stmt->close();
    						return 1;
    					}
    				}
    				else {
    					return 3;
    				}
    		}
    		else {
    			return 4;
    		}
                
		}     */

		public function createUser(
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
        ) {
			if($username != ""){
				if( $email != ""){ 
					if($password != ""){
						
						if(!empty($_SERVER['HTTP_CLIENT_IP'])){
						  $ip=$_SERVER['HTTP_CLIENT_IP'];
						//Is it a proxy address
						}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
						  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
						}else{
						  $ip=$_SERVER['REMOTE_ADDR'];
						}
						
			/* $stmt = $this->con->prepare("SELECT ip FROM banned_ips WHERE ip = ? AND active = 'yes' LIMIT 1");
			$stmt->bind_param("s", $ip);
			$stmt->execute(); 
			$stmt->store_result();
			$banned_ip = $stmt->num_rows;
			if($banned_ip > 0){
				error_log($ip." tried to register a new user with username:".$username);
				return 6;
			} */
						
			//nickname
			$username = strip_tags($username); // remove html tags
			$username = str_replace(' ', "_", $username); // remove spaces
			//email
			$email = strip_tags($email); // remove html tags
			$email = str_replace(' ', '', $email); // remove spaces
			$email = ucfirst(strtolower($email)); //uppercase first
			//password
			$password = strip_tags($password); // remove html tags
			
			$password = trim(password_hash($password, PASSWORD_DEFAULT)); // HASH password before sending to database
					// generate username
			$usernamelower = strtolower($username);
			
			// Profile picture assignment
			$rand = rand(1, 2); //random number between 1 and 2

			$date = date("Y-m-d"); // gets current date

			if($rand == 1)
			$profile_pic = "assets/images/profile_pics/defaults/sabotblack.gif";
			else if($rand == 2)
			$profile_pic = "assets/images/profile_pics/defaults/sabotwhite.gif";
			
			$stmt2 = $this->con->prepare("INSERT INTO `email_list` (`id`, `username`, `email`, `removed`, `last_notified`) VALUES (NULL, ?, ?, 'no', CURRENT_TIMESTAMP)");
			$stmt2->bind_param("ss",$username,$email);
			$stmt2->execute();
			$stmt2->close();

			$stmt = $this->con->prepare("INSERT INTO users (`id`, `nickname`, `username`, `description`, `verified`, `email`, `password`, `signup_date`, `profile_pic`, `cover_pic`, `num_posts`, `num_likes`, `user_closed`, `user_banned`, `following_array`,`followers_array`, `blocked_array`, `followings`, `followers`, `friend_array`, `games_followed`, `twitch`, `mixer`, `psn`, `xbox`, `steam`, `discord`, `user_level`, `last_ip`, `last_online`, `noti_token`) VALUES (NULL, ?, ?,'','', ?, ?, ?, ?,'', '0', '0', 'no','no', ',', ',', ',', '0', '0', ',', ',', '', '', '', '', '', '',  '0', '0', '$date', '')");
			$stmt->bind_param("ssssss",
			$username,
			$usernamelower,
			$email,
			$password,
			$date,
			$profile_pic);
			if($stmt->execute()){
				$stmt->close();
				if($howdidyoufindus!=null&&$howdidyoufindus!==""){
					$stmt2 = $this->con->prepare("INSERT INTO `user_howdidyoufindus` (`id`, `username`, `text`, `date`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP)");
					$stmt2->bind_param("ss",$username,$howdidyoufindus);
					$stmt2->execute();
					$stmt2->close();
				}
				return 1;
			}else{
				return 2;
				return false;
			}
		}else{
			return 3;
			return false;
		}}else{
			return 4;
			return false;
		}}else{
			return 5;
			return false;
		}
		}
		

		public function createUserOLD($username, $email, $password, $howdidyoufindus){

			if($this->isUserExist($username,$email)){
				return 0; 
			}else{
			    if($username != ""){
			        if( $email != ""){ 
			            if($password != ""){
			                
			                if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                              $ip=$_SERVER['HTTP_CLIENT_IP'];
                            //Is it a proxy address
                            }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                              $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
                            }else{
                              $ip=$_SERVER['REMOTE_ADDR'];
                            }
                            
                $stmt = $this->con->prepare("SELECT ip FROM banned_ips WHERE ip = ? AND active = 'yes' LIMIT 1");
    			$stmt->bind_param("s", $ip);
    			$stmt->execute(); 
    			$stmt->store_result();
    			$banned_ip = $stmt->num_rows;
    			if($banned_ip > 0){
    			    error_log($ip." tried to register a new user with username:".$username);
    			    return 6;
    			}
			                
			    //nickname
            	$username = strip_tags($username); // remove html tags
            	$username = str_replace(' ', "_", $username); // remove spaces
            	//email
            	$email = strip_tags($email); // remove html tags
            	$email = str_replace(' ', '', $email); // remove spaces
            	$email = ucfirst(strtolower($email)); //uppercase first
            	//password
            	$password = strip_tags($password); // remove html tags
			    
				$password = trim(password_hash($password, PASSWORD_DEFAULT)); // HASH password before sending to database
						// generate username
				$usernamelower = strtolower($username);
				
				// Profile picture assignment
				$rand = rand(1, 2); //random number between 1 and 2

				$date = date("Y-m-d"); // gets current date

				if($rand == 1)
				$profile_pic = "assets/images/profile_pics/defaults/sabotblack.gif";
				else if($rand == 2)
				$profile_pic = "assets/images/profile_pics/defaults/sabotwhite.gif";
				
				$stmt2 = $this->con->prepare("INSERT INTO `email_list` (`id`, `username`, `email`, `removed`, `last_notified`) VALUES (NULL, ?, ?, 'no', CURRENT_TIMESTAMP)");
                $stmt2->bind_param("ss",$username,$email);
                $stmt2->execute();
                $stmt2->close();

				$stmt = $this->con->prepare("INSERT INTO users (`id`, `nickname`, `username`, `description`, `verified`, `email`, `password`, `signup_date`, `profile_pic`, `cover_pic`, `num_posts`, `num_likes`, `user_closed`, `user_banned`, `following_array`,`followers_array`, `blocked_array`, `followings`, `followers`, `friend_array`, `games_followed`, `twitch`, `mixer`, `psn`, `xbox`, `steam`, `discord`, `user_level`, `last_ip`, `last_online`, `noti_token`) VALUES (NULL, ?, ?,'','', ?, ?, ?, ?,'', '0', '0', 'no','no', ',', ',', ',', '0', '0', ',', ',', '', '', '', '', '', '',  '0', '0', '$date', '')");
				$stmt->bind_param("ssssss",
				$username,
				$usernamelower,
				$email,
				$password,
				$date,
				$profile_pic);
				if($stmt->execute()){
					$stmt->close();
					if($howdidyoufindus!=null&&$howdidyoufindus!==""){
					    $stmt2 = $this->con->prepare("INSERT INTO `user_howdidyoufindus` (`id`, `username`, `text`, `date`) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP)");
                        $stmt2->bind_param("ss",$username,$howdidyoufindus);
                        $stmt2->execute();
                        $stmt2->close();
					}
					return 1;
				}else{
					return 2;
					return false;
				}
			}else{
			    return 3;
			    return false;
			}}else{
			    return 4;
			    return false;
			}}else{
			    return 5;
			    return false;
			}
			}
		}

		public function userLogin($email, $password){
			    $get_password = $this->con->prepare("SELECT password FROM users WHERE email=? LIMIT 1");
			    $get_password->bind_param("s",$email); 
                $get_password->execute();
                $get_password->bind_result($db_password);
                $get_password->store_result();
			    $result = $get_password->num_rows;
			    if( $result > 0 ){
			       $get_password->fetch();
			       $get_password->close();
			    } else {
			        $db_password = "";
			    }
            if( password_verify( $password, $db_password ) ){
                $validate = $this->con->prepare("SELECT username FROM users WHERE email=? AND password=? LIMIT 1" );
			    $validate->bind_param("ss",$email,$db_password); 
                $validate->execute();
                $validate->bind_result($username1);
                $validate->store_result();
                $validate_check = $validate->num_rows;
                if( $validate_check == 1 ){
                    $validate->fetch();
                    $validate->close();
                    $date_added = date("Y-m-d H:i:s");
                    
                    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                      $ip=$_SERVER['HTTP_CLIENT_IP'];
                    //Is it a proxy address
                    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
                    }else{
                      $ip=$_SERVER['REMOTE_ADDR'];
                    }
                    
                    $stmt9 = $this->con->prepare("UPDATE `users` SET `last_ip` = ?, `last_online` = ? WHERE `users`.`username` = ? LIMIT 1");
        			$stmt9->bind_param("sss",$ip,$date_added,$username1);
        			$stmt9->execute();
                    $stmt9->close();
                    
                    $user_closed = $this->con->prepare("SELECT user_closed FROM users WHERE username=? AND user_closed ='yes' LIMIT 1");
        			$user_closed->bind_param("s",$username1);
                    $user_closed->execute();
                    $user_closed->store_result();
                    if($user_closed->num_rows == 1 ){
                        $user_closed->close();
                        $reopen_account = $this->con->prepare("UPDATE users SET user_closed='no' WHERE username=?" );
		                $reopen_account->bind_param("s",$username1); 
                        $reopen_account->execute();
                        $reopen_account->close();
                    }
                        
                } else {
                    error_log("Validate check failed!");
                    return false;
                }
            } else {
                return false;
            }
			return true;
		}

		public function getUserByUsername($username){
		$stmt = $this->con->prepare("SELECT id, nickname, username, email, profile_pic, following_array, games_followed, friend_array FROM users WHERE username = ?");
    $stmt->bind_param("s",$username);
    $stmt->execute(); // Execute the prepared statement
    $stmt->store_result(); // Store the prepared statement for later checking

    // Check to make sure if any data is returned
    if($stmt->num_rows > 0) {

        // Create and append variables to the appropriate columns
        $stmt->bind_result($id, $nickname, $username2, $email, $profile_pic, $users_followed, $games_followed, $friend_array);
        error_log($blocked_array);

        // Create a while loop
        while($stmt->fetch()) {
                $userArray= array(
                    'id' => $id,
                    'nickname' => $nickname,
                    'username' => $username2,
                    'email' => $email,
                    'profile_pic' => $profile_pic,
                    'users_followed' => $users_followed,
                    'games_followed' => $games_followed,
                    'users_friends' => $friend_array
                );
            return $userArray;
        }
			
		}else {
	    return false;
	    }
	} 
	
	public function getUserByEmail($email){
		$stmt = $this->con->prepare("SELECT id, nickname, username, email, profile_pic, following_array, games_followed, friend_array, blocked_array FROM users WHERE email = ?");
        $stmt->bind_param("s",$email);
        $stmt->execute(); // Execute the prepared statement
        $stmt->store_result(); // Store the prepared statement for later checking
        // Check to make sure if any data is returned
        if($stmt->num_rows > 0) {
            // Create and append variables to the appropriate columns
            $stmt->bind_result($id, $nickname, $username, $email2, $profile_pic, $users_followed, $games_followed, $friend_array, $blocked_array);
    
            // Create a while loop
            while($stmt->fetch()) {
                    $userArray= array(
                        'id' => $id,
                        'nickname' => $nickname,
                        'username' => $username,
                        'email' => $email2,
                        'profile_pic' => $profile_pic,
                        'users_followed' => $users_followed,
                        'games_followed' => $games_followed,
                        'users_friends' => $friend_array,
                    'blocked_array' => $blocked_array
                    );
                return $userArray;
            }
		}else {
	        return false;
	    }
	} 
		

	private function isUserExist($username, $email){
		$stmt = $this->con->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
		$stmt->bind_param("ss", $username, $email);
		$stmt->execute(); 
		$stmt->store_result(); 
		return $stmt->num_rows > 0; 
	}

}