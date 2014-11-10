<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* --------------------------------------------------------------------------------------------------
*
* Class MY_Session
* 
* Manages the application session using Redis. Extended from CI_Session so we can use the same
* methods presented there.
* 
* The difference between this class and the original CI_Session is that when you call the methods
* $this->session->set_userdata('key', 'value') and $this->session->userdata('key', 'value') you will
* getting and setting the values from array $_SESSION, which now will be stored in a Redis server. 
*
* @since    09/11/2014
* @author   github: leandrozack <leandro.w3c@gmail.com>
* @license  MIT
*
* --------------------------------------------------------------------------------------------------
*/
// First we load the Predis autoloader
require('application/libraries/predis-1.0/autoload.php');

// Registering Predis system
Predis\Autoloader::register();
 
class MY_Session extends CI_Session implements SessionHandlerInterface {

    public $ttl = 1800; // 30 minutes default
    private $prefix;
    private $redis;
    private $redis_config = ['scheme' => 'tcp', 'host' => '127.0.0.1', 'port' => '6379'];
    
    /**
    * __construct()
    * Class constructor.
    * @return object
    */
    public function __construct($params = array(), $prefix = 'PHPSESSID:') {

        // Try to connect on the Redis server
        $this->redis = new Predis\Client( $this->redis_config );
        $this->prefix = $prefix;

        // Set the PHP SESSION handler as this own class
        session_set_save_handler($this);
        session_start();
    }
    
    // -------------------------------------------------------
    // Mandatory @Overrides defined by SessionHandlerInterface
    // -------------------------------------------------------

    /**
    * @Override
    * open()
    * Create new session, or re-initialize existing session. Called internally by 
    * PHP when a session starts either automatically or when session_start() is invoked.
    * @param string $save_path
    * @param string $session_name
    * @return boolean
    */
    public function open($save_path, $session_name) {
        // No action necessary because connection is created
        // in constructor and arguments are not applicable.
        return true;
    }
    
    /**
    * @Override
    * close()
    * Closes the current session. This method is automatically executed internally by 
    * PHP when closing the session, or explicitly via session_write_close().
    * @return boolean
    */
    public function close() {
        $this->redis = null;
        unset($this->redis);
        return true;
    }
    
    /**
    * @Override
    * read()
    * Reads the session data from the session storage, and returns the result back to 
    * PHP for internal processing. This method is called automatically by PHP when a 
    * session is started, either automatically or explicity with session_start(). The given $id 
    * is the SESSION ID, not a key from array $_SESSION. It will be automatically called when some
    * value is readed from $_SESSION array.
    * @param string $id
    * @return string $sess_data
    */
    public function read ($id = '') {
        $id = $this->prefix . $id;
        $sess_data = $this->redis->get($id);
        $this->redis->expire($id, $this->ttl);
        return $sess_data;
    }
    
    /**
    * @Override
    * write()
    * Writes the session data to the session storage. Called by normal PHP shutdown, by 
    * session_write_close(), or Session handler writing method. The given $id is the 
    * SESSION ID, not a key from array $_SESSION. It will be automatically called when some
    * value will be stored on $_SESSION array.
    * @param string $id
    * @return boolean
    */
    public function write ($id = '', $data = '') {
        $id = $this->prefix . $id;
        $this->redis->set($id, $data);
        $this->redis->expire($id, $this->ttl);
        return true;
    }
    
    /**
    * @Override
    * destroy()
    * Destroys a session. Called by internally by PHP with session_regenerate_id() ,
    * assuming the $destory is set to TRUE, by session_destroy() or when session_decode() fails.
    * @param string $id
    * @return boolean
    */
    public function destroy($id = '') {
        $this->redis->del($this->prefix . $id);
        return true;
    }
    
    /**
    * @Override
    * gc()
    * Cleanup expired session. Called randomly by PHP internally when a session 
    * starts or when session_start() is invoked.
    * @param int $maxLifetime
    * @return boolean
    */
    public function gc($maxLifetime = 0) {
        // no action necessary because using EXPIRE
        return true;
    }


    // ------------------------------------
    // @Overrides from the class CI_Session
    // ------------------------------------

    /**
    * userdata()
    *
    * Fetch a specific item from the $_SESSION array. Returns FALSE in case of does not exist.
    *
    * @param   string
    * @return  string
    */
    function userdata ($item = '') 
    {
        return ( ! isset( $_SESSION[$item] ) ) ? FALSE : $_SESSION[$item];
    }

    /**
    * all_userdata()
    *
    * Returns the $_SESSION array.
    *
    * @param   string
    * @return  string
    */
    function all_userdata () 
    {
        return $_SESSION;
    }

    /**
    * set_userdata()
    *
    * Add or change data in the $_SESSION array.
    *
    * @param   mixed
    * @param   string
    * @return  void
    */
    function set_userdata($key = array(), $value = '')
    {
        if ( is_array($key) ) {
            foreach($key as $new_key => $value)
                $this->set_userdata($new_key, $value);
        } else
            $_SESSION[$key] = $value;
    }

    /**
    * unset_userdata()
    *
    * Deletes a variable data in the $_SESSION array.
    *
    * @param   mixed
    * @param   string
    * @return  void
    */
    function unset_userdata($key = '')
    {
        unset($_SESSION[$key]);
    }

    /**
    * sess_destroy()
    *
    * Destroy the current session.
    *
    * @return  void
    */
    function sess_destroy()
    {
        session_destroy();
    }
}