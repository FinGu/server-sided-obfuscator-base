<?php
//BETA VERSION OF THE PHP's API
//there's no traffic encryption in here, as php is a server sided language
if(!isset($_SESSION))
    session_start();

class c_api{
    public static function c_init($c_version, $c_program_key, $c_api_key){
        try{
            $ch = curl_init(self::$api_link . "ins_handler.php?type=init");
            curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_PINNEDPUBLICKEY, self::$pub_key);

            $_SESSION["program_key"] = $c_program_key;
            $_SESSION["api_key"] = $c_api_key;

            $values = [
                "version" => $c_version,
                "api_version" => "3.1b",
                "program_key" => $_SESSION["program_key"],
                "api_key" => $_SESSION["api_key"]
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, $values);

            $result = curl_exec($ch); curl_close($ch);

            switch($result){
                case "program_doesnt_exist":
                    die("the program doesnt exist");
                    break;

                case "invalid_api_key":
                    die("invalid API Key");
                    break;

                case "wrong_version":
                    die("wrong program version");
                    break;

                case "old_api_version":
                    die("please download the newest api version on the auth's website");
                    break;

                default:
                    break;
            }
        }
        catch(Exception $ex){
            die($ex->getMessage());
        }
    }
    public static function c_login($c_username, $c_password){
        $ch = curl_init(self::$api_link . "ins_handler.php?type=login");
        curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PINNEDPUBLICKEY, self::$pub_key);

        $values = [
            "username" => $c_username,
            "password" => $c_password,
            "program_key" => $_SESSION["program_key"],
            "api_key" => $_SESSION["api_key"]
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $values);

        $result = json_decode(curl_exec($ch)); curl_close($ch);

        switch($result->{'result'}){
            case "invalid_username":
                c_api::alert("invalid username");
                return false;

            case "invalid_password":
                c_api::alert("invalid password");
                return false;

            case "user_is_banned":
                c_api::alert("The user is banned");
                return false;

            case "no_sub":
                c_api::alert("no sub");
                return false;

            case "logged_in":
                $_SESSION["username"] = $result->{'username'};
                $_SESSION["email"] = $result->{'email'};
                $_SESSION["expires"] = $result->{'expires'};
                $_SESSION["rank"] = $result->{'rank'};
                //saved to a session because i cant save the values to a static class

                c_api::alert("logged in");
                return true;

            default:
                die($result);
                break;
        }
    }
    public static function c_register($c_username, $c_email, $c_password, $c_token){
        $ch = curl_init(self::$api_link . "ins_handler.php?type=register");
        curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PINNEDPUBLICKEY, self::$pub_key);

        $values = [
            "username" => $c_username,
            "email" => $c_email,
            "password" => $c_password,
            "token" => $c_token,
            "program_key" => $_SESSION["program_key"],
            "api_key" => $_SESSION["api_key"]
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $values);

        $result = curl_exec($ch); curl_close($ch);

        switch($result){
            case "user_already_exists":
                c_api::alert("user already exists");
                return false;

            case "email_already_exists":
                c_api::alert("email already exists");
                return false;

            case "invalid_email_format":
                c_api::alert("invalid email format");
                return false;

            case "invalid_token":
                c_api::alert("invalid token");
                return false;

            case "maximum_users_reached":
                c_api::alert("maximum users reached");
                return false;

            case "used_token":
                c_api::alert("used token");
                return false;

            case "success":
                c_api::alert("success");
                return true;

            default:
                die($result);
                break;
        }

    }
    public static function c_activate($c_username, $c_password, $c_token){
        $ch = curl_init(self::$api_link . "ins_handler.php?type=activate");
        curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PINNEDPUBLICKEY, self::$pub_key);

        $values = [
            "username" => $c_username,
            "password" => $c_password,
            "token" => $c_token,
            "program_key" => $_SESSION["program_key"],
            "api_key" => $_SESSION["api_key"]
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $values);

        $result = curl_exec($ch); curl_close($ch);

        switch($result){
            case "invalid_username":
                c_api::alert("invalid username");
                return false;

            case "invalid_password":
                c_api::alert("invalid password");
                return false;

            case "user_is_banned":
                c_api::alert("The user is banned");
                return false;

            case "invalid_token":
                c_api::alert("invalid token");
                return false;

            case "used_token":
                c_api::alert("used token");
                return false;

            case "success":
                c_api::alert("success");
                return true;

            default:
                die($result);
                break;
        }
    }
    public static function c_all_in_one($c_token){
        if(c_api::c_login($c_token, $c_token))
            return true;

        else if(c_api::c_register($c_token, $c_token . "@gmail.com", $c_token, $c_token))
            return true;

        else return false;
    }
    //no need for server sided variables here cause php already is server side
    public static function c_log($c_message){
        if(empty($_SESSION["username"]) || !isset($_SESSION["username"])) $_SESSION["username"] = "NONE";

        $ch = curl_init(self::$api_link . "ins_handler.php?type=log");
        curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_PINNEDPUBLICKEY, self::$pub_key);

        $values = [
            "username" => $_SESSION["username"],
            "message" => $c_message,
            "program_key" => $_SESSION["program_key"],
            "api_key" => $_SESSION["api_key"]
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $values);

        curl_exec($ch); curl_close($ch);
    }

    public static function alert($string){
        echo "<script type=\"text/javascript\">";
            echo "alert(\"$string\")"; //ugly
        echo "</script>";
    }
    private static $api_link = "https://cauth.me/api/";
    private static $user_agent = "Mozilla cAuth";
    private static $pub_key = "sha256//Mk6vhbkCoRzUhXoUryC8tjIxmehtu4uLVhwqGQM9Cmc=";
}
