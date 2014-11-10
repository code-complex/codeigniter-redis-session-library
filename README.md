# CodeIgniter Redis Session Library

This library acts at the same time as a Session handler and a CodeIgniter Session Library override. It was created with the purpose to store the session data in a Redis server.

### Requirements
- PHP >= 5.4
- CodeIgniter >= 2.2.x
- Predis library >= 1.0.0 http://github.com/nkr/predis

### Installation
- Download the Predis library and put it in the application/libraries folder
- Put ```MY_Session.php``` inside application/libraries folder
- Change the Redis server configuration through the ```$redis_config``` attribute in MY_Session.php. Put the parameters from the Redis server you attempting to connect
- That is it! 

### How it works
This class implements some abstract methods from PHP ```SessionHandlerInterface``` class, which is only presented on PHP 5.4. For this reason this class **will only works in PHP 5.4**. Once you get this class you will be overriding the session handling, setting this library as handler.

This library also override the native CI_Session allowing you to store data in the $_SESSION array instead of creating local cookies. So when you call:

```php
$this->session->set_userdata('some_key', 'some_value');
```

You will be setting this value on the $_SESSION array, which now is stored on the Redis server. The same thing happens when you retrieve data from session, as the following command:

```php
$this->session->userdata('some_key'); // you are retrieving from $_SESSION array now, stored on Redis server :)
```

### Overwritten methods
This class only has implemented/overwritten only the following CI_Session native public methods:
- ```$this->session->userdata( $key );```
- ```$this->session->userdata( $key );```
- ```$this->session->set_userdata( $key, $value );```
- ```$this->session->unset_userdata( $key );```
- ```$this->session->sess_destroy( $key );```

### Non-overwritten methods
This class **has not** implemented/overwritten the following CI_Session native public methods:
- ```$this->session->flashdata( $key );```
- ```$this->session->set_flashdata( $key, $value );```
- ```$this->session->keep_flashdata( $key );```

### License
Developed under MIT license. Feel fre to use, improve and report any found issue.