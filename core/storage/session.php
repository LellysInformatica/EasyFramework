<?php

/*
  Class: Session

  Handles session handling using native PHP sessions. This class
  provides basic support for reading, writing and deleteing session
  data. Also, it supports more high-level actions such as flashing
  messages between different requests.

  Todo:
  - Use adapters for different session storages.
 */

class Session {

    /**
     * Current Session id
     *
     * @var string
     */
    public static $id = null;

    /*
      Constant: FLASH_KEY

      Name of the key used to store "flash" messages between requests.
     */
    const FLASH_KEY = 'Flash';

    /*
      Variable: $options

      Params for the session's cookie. The available options are

      lifetime - lifetime of the session cookie, in seconds. 0 will
      keep the cookie until the user's browser is closed.
      path - path on the domain where the cookie will work. '/' works
      for all paths in the domain and is the default.
      domain - cookie domain. To make a cookie available to all
      subdomais, prefix it with a '.'. Default is '', making
      it available to the current domain only.
      secure - if true, cookie will be sent only over secure
      connections.
      httponly - if true, 'httponly' flag will be sent with the cookie,
      disallowing access to the cookie through JavaScript.
      It is extremely recommended for security reasons.
     */

    protected static $options = array(
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true
    );

    /*
      Method: start

      Starts and configures the session.

      Returns:
      True if the session successfully started (or has already
      been started), false otherwise.
     */

    public static function start() {
        if (self::started()) {
            return true;
        }
        $id = self::id();
        session_write_close();
        self::setCookieParams();
        session_start();

        return self::started();
    }

    /*
      Method: started

      Verifies if the session was already started.

      Returns:
      True if the session was already started, false otherwise.
     */

    public static function started() {
        return isset($_SESSION) && session_id();
    }

    /**
     * Returns true if given variable is set in session.
     *
     * @param string $name Variable name to check for
     * @return boolean True if variable is there
     */
    public static function check($name = null) {
        if (!self::started() && !self::start()) {
            return false;
        }
        if (empty($name)) {
            return false;
        }
        $result = self::read($name);
        return isset($result);
    }

    /*
      Method: read

      Reads a value from the session.

      Params:
      $name - key of the entry to be read.

      Returns:
      The value from the session, null if the key is not defined.

      Throws:
      - RuntimeException if the session can't be started.
     */

    public static function read($key) {
        if (!self::started() && !self::start()) {
            return false;
        }
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
    }

    /*
      Method: write

      Writes a value to the session.

      Params:
      $key - key of the value to be stored.
      $value - value to be stored.

      Throws:
      - RuntimeException if the session can't be started.
     */

    public static function write($key, $value) {
        if (!self::started() && !self::start()) {
            return false;
        }
        $_SESSION[$key] = $value;
    }

    /*
      Method: delete

      Deletes a value from the session.

      Params:
      $key - key of the value to be deleted.

      Throws:
      - RuntimeException if the session can't be started.
     */

    public static function delete($name) {
        if (!self::started() && !self::start()) {
            return false;
        }

        unset($_SESSION[$name]);
    }

    /**
     * Helper method to destroy invalid sessions.
     *
     * @return void
     */
    public static function destroy() {
        if (self::started()) {
            session_destroy();
        }
        self::clear();
    }

    /**
     * Clears the session, the session id, and renew's the session.
     *
     * @return void
     */
    public static function clear() {
        $_SESSION = null;
        self::$id = null;
        self::start();
        self::renew();
    }

    /*
      Method: writeFlash

      Writes a "flash" message to the session. A flash message is
      used to persist a message between requests (a success or error
      message, for example), and is deleted as soon as it is read.

      Params:
      $key - key of the value to be stored.
      $value - value to be stored.


      Throws:
      - RuntimeException if the session can't be started.

      See Also:
      <Session::flash>
     */

    public static function writeFlash($key, $value) {
        self::write(self::FLASH_KEY . '.' . $key, $value);
    }

    /*
      Method: flash

      Reads or writes a "flash" message to the session. A flash message
      is used to persist a message between requests (a success or error
      message, for example), and is deleted as soon as it is read. This
      method can be used instead of <Session::writeFlash> by passing a
      second parameter. This method also deletes the key from the
      session when used for reading.

      Params:
      $key - key of the value to be read or stored.
      $value - value to be stored. If null, the previous key will
      be read instead of written.

      Returns:
      The value from the session when reading (and when the key
      is set), null otherwise.

      Throws:
      - RuntimeException if the session can't be started.

      See Also:
      <Session::writeFlash>
     */

    public static function flash($key, $value = null) {
        if (!is_null($value)) {
            self::writeFlash($key, $value);
        } else {
            $flash = self::FLASH_KEY . '.' . $key;
            $value = self::read($flash);
            self::delete($flash);

            return $value;
        }
    }

    /**
     * Returns the Session id
     *
     * @param string $id
     * @return string Session id
     */
    public static function id($id = null) {
        if ($id) {
            self::$id = $id;
            session_id(self::$id);
        }
        if (self::started()) {
            return session_id();
        }
        return self::$id;
    }

    /*
      Method: regenerate

      Regenerates the current session's id. It also reapplies the
      session's cookie options.

      Throws:
      - RuntimeException if the session can't be started.
     */

    public static function renew() {
        if (session_id()) {
            if (session_id() != '' || isset($_COOKIE[session_name()])) {
                self::setCookieParams();
            }
            session_regenerate_id(true);
        }
    }

    /*
      Method: option

      Sets options for the session's cookie. Note that you have to
      call this method before starting the session. If the session
      was already started and you need to set new values for the
      cookie, use <Session::regenerate>.

      Params:
      $option - option to be set.
      $value - value of the option.

      Throws:
      - RuntimeException if the session was already started.

      See Also:
      <Session::$options>, <Session::regenerate>
     */

    public static function option($option, $value) {
        self::$options[$option] = $value;
    }

    /*
      Method: setCookieParams

      Set params for the session's cookie.
     */

    protected static function setCookieParams() {
        session_name(md5('sal' . $_SERVER['REMOTE_ADDR'] . 'sal' . $_SERVER['HTTP_USER_AGENT'] . 'sal'));
        session_set_cookie_params(
                self::$options['lifetime'], self::$options['path'], self::$options['domain'], self::$options['secure'], self::$options['httponly']
        );
    }

}