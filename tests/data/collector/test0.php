<?php

   /**
    * fCMS SiteSystem core
    *
    * PHP Version 5.2
    *
    * @category  Core
    * @package   Core
    * @author    Arne Blankerts <theseer@fcms.de>
    * @copyright 2008 fCMS Development Team
    * @license   http://fcms.de/en/site/license.xml freepoint public license
    * @version   CVS: $Id: fdebug.lib.fcmsv5.php 9 2009-01-05 19:10:48Z theseer $
    *
    */

   /**
    * fDebug class
    *
    * @category Core
    * @package  Core
    * @access   public
    * @author   Arne Blankerts <theseer@fcms.de>
    *
    */
   class fDebug implements fSingleton {

      /**
       * version constant for debugging purposes
       */
      const version = '$Revision: 111 $ ($Date: 2009-11-01 16:37:42 +0100 (Sun, 01 Nov 2009) $)';

      /**
       * Message type constant
       */
      const MESSAGE = 'MESSAGE';

      /**
       * Message type constant
       */
      const WARNING = 'WARNING';

      /**
       * Message type constant
       */
      const ERROR = 'ERROR';

      /**
       * Message type constant
       */
      const FATAL = 'FATAL';

      /**
       * Instance of class
       *
       * @var fDebug
       */
      protected static $instance = null;

      /**
       * Socket handle
       *
       * @var socket
       */
      protected $socket = null;

      /**
       * Logfile to use in file mode
       *
       * @var string
       */
      protected $logFile = null;

      /**
       * Sourcecode to dump
       *
       * @var string
       */
      protected $source = '';

      /**
       * Variable reference storage
       *
       * @var Array
       */
      protected $variables = array();

      /**
       * current context
       *
       * @var string
       */
      protected $context = 'fCore';

      /**
       * Backlog of contexts
       *
       * @var array
       */
      protected $previousContext = array('fCore');

      /**
       * Flag to automagically set context to calling class
       *
       * @var boolean
       */
      protected $autoContext = false;

      /**
       * Remember if source has been sent
       *
       * @var boolean
       */
      protected $sourceSent = false;

      /**
       * Remember if variable dump has been sent
       *
       * @var boolean
       */
      protected $varsSent = false;

      /**
       * Hostname of debug session
       *
       * @var string
       */
      protected $sessionHost = '{undefined hostname}';

      /**
       * Url of debug session
       *
       * @var string
       */
      protected $sessionUri = '{undefined uri}';


      /**
       * Disallow construct from outside
       *
       * @return void
       */
      protected function __construct() {
      }

      /**
       * Destructor
       *
       * @return void
       */
      public function __destruct() {

         if ($this->isConnected()) {
            if (!$this->varsSent) $this->sendVariables();
            if (!$this->sourceSent) $this->sendSource();
            fflush($this->socket);
            $this->writeSocket('CONTROL', array('action' => 'QUIT'));
            @fflush($this->socket);
            @fclose($this->socket);
            $this->socket = null;
         }
      }

      /**
       * get or create instance of fDebug
       *
       * @return fDebug
       */
      public static function getInstance() {
         if (self::$instance == null) {
            self::$instance = new fDebug();
         }
         return self::$instance;
      }

      /**
       * setup session details
       *
       * @param string $host Hostname this session is for
       * @param string $url  Url this session is for
       *
       * @return void
       */
      public function setSession($host, $url) {
         $this->sessionHost = $host;
         $this->sessionUri  = $url;
      }

      /**
       * open a new socket connection
       *
       * @param string  $remote ip address of target
       * @param integer $port   (optional) port on target host (default: 5005)
       *
       * @return boolean true on success, false on failure
       */
      public function openSocket($remote, $port = 5005) {
         if ($this->isConnected()) {
            $this->closeSocket();
         }
         if ($this->connectRemote($remote, $port)) {
            $this->sendHelo();
         }
      }

      /**
       * open a new socket connection to an fdebug proxy
       *
       * @param string  $uuid  Unique client id
       * @param string  $proxy IP address of proxy server
       * @param integer $port  (optional) Port on the proxy to connect to (default: 5005)
       *
       * @return boolean true on success, false on failure
       */
      public function openProxy($uuid, $proxy, $port = 5005) {

         if ($this->connectRemote($proxy, $port)) {

            $this->writeSocket('SETCLIENT', array(
               'UUID' => $uuid
               ));

            $this->sendHelo();

            return true;
         }

         return false;
      }

      /**
       * create socket connection
       *
       * @param string  $remote IP-Address or hostname
       * @param integer $port   Port
       *
       * @return boolean true on success, false on failure
       */
      public function connectRemote($remote, $port) {
         $timeout = $remote=='127.0.0.1' ? 1 : 5;

         $this->socket = @fsockopen($remote, $port, $errno, $errstr, $timeout);
         if (!$this->socket) {
            $this->socket = null;
            return false;
         }
         return true;
      }

      /**
       * Initiate fDebug session
       *
       * @return void
       *
       */
      public function sendHelo() {
         $this->writeSocket('CONTROL', array(
               'action' => 'HELO',
               'url'    => $this->sessionUri,
               'server' => $this->sessionHost
         ));

         // ping "server" to verify connection
         $this->writeSocket('CONTROL', array('action' => 'PING'));
      }

      /**
       * open debug logfile
       *
       * @param string $fname filename of the logfile
       *
       * @return boolean true on success, false on failure
       */
      public function openFile($fname) {
         if (!touch($fname)) {
            return false;
         }
         $this->logFile = $fname;
         return true;
      }

      /**
       * Close an open socket connection
       *
       * @return void
       */
      public function closeSocket() {
         if (!$this->isConnected()) {
            return;
         }
         fclose($this->socket);
         $this->socket = null;
      }

      /**
       * Verify if there is an active connection
       *
       * @return boolean true if there is a connection otherwise false
       */
      public function isConnected() {
         return $this->socket != null;
      }

      /**
       * Verify if there is an active session
       *
       * Use this method to check if there is either a socket connection or logfile or both available
       *
       * @return boolean true if there is a session otherwise false
       */
      public function hasSession() {
         return ($this->socket != null || $this->logFile != null);
      }

      /**
       * Write data to an active socket connection
       *
       * @param string $type    Message type to be send
       * @param array  $payload Payload data depending on type
       *
       * @return void
       */
      protected function writeSocket($type, Array $payload) {
         if (!$this->isConnected()) {
            return;
         }

         $obj = new StdClass;
         $obj->type    = $type;
         $obj->payload = new StdClass;
         foreach ($payload as $key => $value) {
            $obj->payload->$key = $value;
         }

         $str = json_encode($obj)."\n";
         $x   = mb_strlen($str);
         $rc  = @fwrite($this->socket, $str, mb_strlen($str));
         $rc2 = @fflush($this->socket);
         if (!$rc || ($x != $rc)) {
            $this->closeSocket();
            return;
         }

         $reply = @fgets($this->socket, 6);

         if (trim($reply)=='ERROR' || $reply===false) {
            $this->closeSocket();
         }

      }

      /**
       * Write to logfile
       *
       * @param array $payload Payload data array
       *
       * @return void
       */
      protected function writeFile($payload) {
         if (is_null($this->logFile)) {
            return;
         }

         $logText = sprintf(
            "[%s] %s [%s%s() (%s, line %s)]\n",
            $payload['level'],
            $payload['message'],
            empty($payload['class']) ? '' : '$'.$payload['class'].$payload['type'],
            $payload['method'],
            $payload['file'],
            $payload['line']
         );

         error_log($logText, 3, $this->logFile);
      }

      /**
       * Set the Source for dumping later
       *
       * @param string $source Sourcecode to send
       *
       * @return void
       */
      public function setSource($source) {
         $this->source = $source;
      }

      /**
       * Verify if source has been set
       *
       * @return boolean true if there is a source otherwise false
       */
      public function hasSource() {
         return $this->source != null;
      }

      /**
       * Register a custom variable for dumping
       *
       * @param mixed  &$var the variable to dump
       * @param string $key  the name to show variable under
       *
       * @return void
       */
      public function registerVariable(&$var,$key='') {

         if ($key) {
            $this->variables[$key] = &$var;
         } else {
            $this->variables[] = &$var;
         }

      }

      /**
       * Send Variable dump to remote
       *
       * @param array $dumpAdd Additional variables
       *
       * @return void
       */
      public function sendVariables(Array $dumpAdd = array()) {
         $this->varsSent = true;

         if (!$this->socket) { return; }

         $xml = new XMLWriter();
         $xml->openMemory();

         $xml->startDocument('1.0', 'UTF-8');
         $xml->startElement('vardump');

         $this->flattenArray($xml, $_GET, 'GET');
         $this->flattenArray($xml, $_POST, 'POST');
         foreach($this->variables as $key => $value) {
            $this->flattenArray($xml, $value, $key);
         }
         $this->flattenArray($xml, $dumpAdd, 'OTHER');

         if (isset($_SESSION)) {
            $this->flattenArray($xml, $_SESSION, 'SESSION');
         }
         if (isset($_FILES)) {
            $this->flattenArray($xml, $_FILES, 'FILES');
         }
         $this->flattenArray($xml, $_COOKIE, 'COOKIE');
         $this->flattenArray($xml, $_SERVER, 'SERVER');

         $xml->endElement();
         $xml->endDocument();

         $this->writeSocket('VARIABLES', array('xml' => $xml->outputMemory(true)));

      }

      /**
       * Flatten a given Array to xml structure using xmlWriter
       *
       * @param XMLWriter $xml     instance of XMLWriter
       * @param array     $arr     Data array to flatten
       * @param string    $name    variable name
       * @param integer   $nestLvl current nesting level
       * @param string    $type    variable type
       *
       * @return void
       */
      protected function flattenArray(XMLWriter $xml, Array $arr, $name = '', $nestLvl = 0, $type = '') {
         if (count($arr)==0) {
            return;
         }

         $nestLvl++;
         if ($nestLvl>50) {
            $xml->startElement('var');
            $xml->writeAttribute('key', $name);
            $xml->text('nesting level too deep');
            $xml->endElement();
            return;
         }

         $xml->startElement('vargroup');
         $xml->writeAttribute('name', $name);
         $xml->writeAttribute('type', $type);

         foreach ($arr as $key => $value) {
            if (is_array($value)) {
               $this->flattenArray($xml, $value, $key, $nestLvl);
            } elseif (is_object($value)) {
               $targettype = 'Object ('.get_class($value);
               if ($extends = get_parent_class($value)) {
                  $type .= ' extends '.$extends;
               }
               $targettype .= ')';
               $this->flattenArray($xml, get_object_vars($value), $key, $nestLvl, $targettype);

            } else {
               $xml->startElement('var');
               $xml->writeAttribute('key', $key);
               $xml->text($value);
               $xml->endElement();
            }
         }

         $xml->endElement();

         return;
      }

      /**
       * Set a context
       *
       * @param string $context Name of the context
       *
       * @return void
       */
      public function setContext($context = null) {
         if (is_null($context)) {
            if (count($this->previousContext)>1) {
               $this->context = array_pop($this->previousContext);
            }
            return;
         }
         $this->previousContext[] = $this->context;
         $this->context = $context;
      }

      /**
       * Reset context back to previous context
       *
       * @return void
       *
       */
      public function resetContext() {
         $this->setContext();
      }

      /**
       * enable AutoContext switching
       *
       * @return void
       */
      public function enableAutoContext() {
         $this->autoContext = true;
      }

      /**
       * disable AutoContext switching
       *
       * @return void
       */
      public function disableAutoContext() {
         $this->autoContext = false;
      }

      /**
       * Send a Debug message (autosets context to fDebug)
       *
       * @param string $msg Message to send
       *
       * @return void
       */
      public function sendDebug($msg) {
         $this->handleMessage($msg, fdebug::MESSAGE, 'DEBUG');
      }

      /**
       * Send a standard message with an optional given context
       *
       * @param string $msg     Message to send
       * @param string $context (optional) Context to set
       *
       * @return void
       */
      public function sendMessage($msg, $context = null) {
         $this->handleMessage($msg, fdebug::MESSAGE, $context);
      }

      /**
       * Send a Warning with an optional given context
       *
       * @param string $msg     Message to send
       * @param string $context (optional) Context to set
       *
       * @return void
       */
      public function sendWarning($msg, $context = null) {
         $this->handleMessage($msg, fdebug::WARNING, $context);
      }

      /**
       * Send an Error with an optional given context
       *
       * @param string $msg     Message to send
       * @param string $context (optional) Context to set
       *
       * @return void
       */
      public function sendError($msg, $context = null) {
         $this->handleMessage($msg, fdebug::ERROR, $context);
      }

      /**
       * Send a Fatal error with an optional given context
       *
       * @param string $msg     Message to send
       * @param string $context (optional) Context to set
       *
       * @return void
       */
      public function sendFatal($msg, $context = null) {
         $this->handleMessage($msg, fdebug::FATAL, $context);
      }

      /**
       * produce Message object and sent it over socket / write to file
       *
       * @param string $msg     Message to send
       * @param string $level   Type of message (MESSAGE, ERROR, ...)
       * @param string $context (optional) Context info
       *
       * @return void
       */
      protected function handleMessage($msg, $level, $context = null) {
         if (!$this->hasSession()) return;

         //debug_print_backtrace();

         $trace = array_slice(debug_backtrace(), 1, 2);

         if ((isset($trace[1]['class']) && defined($trace[1]['class'].'::version')!='')) {
            $version = constant($trace[1]['class'].'::version');
         } else {
            $version = 'N/A';
         }

         $payload = array(
            'context' => is_null($context) ? $this->context : $context,
            'level'   => $level,
            'message' => (string)$msg,
            'line'    => $trace[0]['line'],
            'class'   => isset($trace[1]['class']) ? $trace[1]['class'] : '',
            'method'  => isset($trace[1]['function']) ? $trace[1]['function'] : 'main',
            'type'    => isset($trace[1]['type']) ? $trace[1]['type'] : '',
            'file'    => basename($trace[0]['file']),
            'version' => $version
         );

         if ($this->autoContext && is_null($context)) {
            $payload['context'] = $payload['class'];
         }

         $this->writeSocket('MESSAGE', $payload);
         $this->writeFile($payload);

      }

      /**
       * Send source to client
       *
       * @param string $xml (optional) Overwrite previously set source
       *
       * @return void
       */
      public function sendSource($xml=null) {
         $this->sourceSent = true;

         if (!$this->isConnected()) { return; }

         if (is_null($xml)) {
            $xml = $this->source;
         }

         if (empty($xml)) {
            $this->sendWarning('Source is empty - cannot send');
            return;
         }

         $this->writeSocket('SOURCE', array('xml' => $xml));

      }

   }
