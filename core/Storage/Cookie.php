<?php

/**
 *  Cookie cuida da criação e leitura de cookies para o EasyFramework, levando em conta
 *  aspectos de segurança, encriptando todos os cookies criados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com) & Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */
class Cookie {

    public $expires;
    public $path = '/';
    public $domain = '';
    public $secure = false;
    public $key;
    public $name = 'EasyCookie';
    public static $instance;

    public function __construct() {
        $this->key = Config::read('Security.salt');
    }

    public static function instance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public static function set($key, $value) {
        $self = self::instance();

        if (isset($self->$key)) {
            $self->$key = $value;
            return true;
        }

        return false;
    }

    public static function get($key) {
        $self = self::instance();

        if (isset($self->$key)) {
            return $self->$key;
        }
    }

    public static function delete($name) {
        $self = self::instance();
        $path = Mapper::normalize(Mapper::base() . $self->path);

        return setcookie($self->name . '[' . $name . ']', '', time() - 42000, $path, $self->domain, $self->secure);
    }

    public static function read($name) {
        $self = self::instance();
        if (array_key_exists($self->name, $_COOKIE)) {
            return self::decrypt($_COOKIE[$self->name][$name]);
        } else {
            return null;
        }
    }

    public static function write($name, $value, $expires = null) {
        $self = self::instance();
        $expires = $self->expire($expires);
        $path = Mapper::normalize(Mapper::base() . $self->path);

        return setcookie($self->name . '[' . $name . ']', self::encrypt($value), $expires, $path, $self->domain, $self->secure, true);
    }

    public static function encrypt($value) {
        $self = self::instance();
        $encripted = base64_encode(Security::cipher($value, $self->key));

        return 'U3BhZ2hldHRp.' . $encripted;
    }

    public static function decrypt($value) {
        $self = self::instance();
        $prefix = strpos($value, 'U3BhZ2hldHRp.');

        if ($prefix !== false) {
            $encrypted = base64_decode(substr($value, $prefix + 13));
            return Security::cipher($encrypted, $self->key);
        }

        return false;
    }

    public function expire($expires) {
        $now = time();

        if (is_numeric($expires)) {
            return $this->expires = $now + intval($expires);
        } else {
            return $this->expires = strtotime($expires, $now);
        }
    }

}

?>