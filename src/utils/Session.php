<?php

class Session
{
    // Start Session
    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Config a session value
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    // Get session value
    public static function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    // Validate if session value exists
    public static function exists($key)
    {
        return isset($_SESSION[$key]);
    }

    // Delete value session
    public static function delete($key)
    {
        if (self::exists($key)) {
            unset($_SESSION[$key]);
        }
    }

    // Destroy session
    public static function destroy()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
            $_SESSION = [];
        }
    }

    // Verify if user is loggedin
    public static function isLoggedIn()
    {
        return self::exists('user_id');
    }
}
