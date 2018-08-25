<?php

/**
 * Date: 2/5/2016
 * Time: 3:48 PM
 */
class SessionManagement {

    public static function sessionStart() {
        if(self::sessionStarted() == false) {
            session_start();
        }
    }

    public static function sessionStarted() {
        if(session_id() == '') {
            return false;
        } else {
            return true;
        }
    }
    public static function sessionExists($session) {
        if(self::sessionStarted() == false) {
            session_start();
        }
        if(isset($_SESSION[$session])) {
            return true;
        } else {
            return false;
        }
    }
    public static function setSession($session, $value) {
        if(self::sessionStarted() != true) {
            session_start();
        }
        $_SESSION[$session] = $value;
        if(self::sessionExists($session) == false) {
            throw new Exception('Unable to Create Session');
        }
    }
    public static function getSession($session) {
        if(isset($_SESSION[$session])) {
            return $_SESSION[$session];
        } else {
            throw new Exception('Session Does Not Exist');
        }
    }
    public static function removeSession($session) {
        if(isset($_SESSION[$session])) {
            unset($_SESSION[$session]);
        } else {
            throw new Exception('Session Does Not Exist');
        }
    }
}