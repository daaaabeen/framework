<?php
/**
 * @Author: beenlee
 * @Date:   2016-03-24 16:59:00
 * @Last Modified by:   dabeen
 * @Last Modified time: 2016-12-21 15:23:49
 */
namespace Beenlee\Framework\Storage;

class Storage {
    
    protected static $_dao = null;

    protected static $_cache = null;

    protected static $_session = null;

    protected static $_cookie = null;

    protected static $_file = null;

    public static function getDao () {
        if (null === self::$_dao) {
            throw new Exception('no dao set!');
        }
        return self::$_dao;
    }

    public static function setDao ($dao) {
        self::$_dao = $dao;
    }


    public static function getCache () {
        if (null === self::$_cache) {
            throw new Exception('no cache access object set!');
        }
        return self::$_cache;
    }

    public static function setCache ($cache) {
        self::$_cache = $cache;
    }
    

    public static function getSession () {
        if (null === self::$_session) {
            return self::$_session = new Session();
        }
        return self::$_session;
    }

    public static function setSession ($session) {
        self::$_session = $session;
    }


    public static function getCookie () {
        if (null === self::$_cookie) {
            return self::$_cookie = new Cookie();
        }
        return self::$_cookie;
    }

    public static function setCookie ($cookie) {
        self::$_cookie = $cookie;
    }


    public static function getFile () {
        if (null === self::$_file) {
            if (null === self::$_file) {
            return self::$_file = new File();
        }
        }
        return self::$_file;
    }

    public static function setFile ($file) {
        self::$_file = $file;
    }

}