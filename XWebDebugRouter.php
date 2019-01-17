<?php

/**
 * TODO 7.12.18:
 * add module name, controller name, layout name, view name,  
 * add paths for module,controller,layout,view
 * add request
 * add User Information (ID, Name, Session)
 */


/*
 * Yii Debug Toolbar
 *
 * Using:
 * 1. First Step.
 * Extract yiidebugtb folder to [webroot]/protected/extensions/yiidebugtb
 * --Test:
 * -- You must have an "[webroot]/protected/extensions/yiidebugtb/XWebDebugRouter.php" file
 * -- if you all right way.
 *
 * 2. Second Step.
 * Open [webroot]/protected/config/main.php, find section 'import' and add following lines which belongs to yiidebugtb:
 *
 * [...]
 * // autoloading model and component classes
 * 'import'=>array(
 * 		'application.models.*',
 * 		'application.extensions.yiidebugtb.*'
 * 		[...]
 * ),
 * [...]
 *
 * 3. And Last Step.
 * In [webroot]/protected/config/main.php find section 'routes' and add following lines for XWebDebugRouter:
 *
 * [...]
 * 'routes'=>array(
 * 		array(
 * 			'class'=>'XWebDebugRouter',
 * 			'config'=>'alignLeft, opaque, runInDebug, fixedPos, collapsed, dbProfiling',
 * 			'levels'=>'error, warning, trace, profile, info',
 *      'allowedIPs'=>array('127.0.0.1','192.168.1.54','192\.168\.1[0-5]\.[0-9]{3}'),
 * 		),
 * ),
 * [...]
 *
 * Config options are mean:
 * 'alignLeft'	=> Debug toolbar will be aligned to the top left corner of browser window
 * 'opaque'		=> Makes debug toolbar almost invisible when it's minimized
 * 'runInDebug'	=> Show debug toolbar only if Yii application running in DEBUG MODE (see index.php for details)
 * 'fixedPos'	=> Makes debug toolbar sticky with browser window, not document!
 * 'collapsed'	=> Show debug toolbar minimized by default.
 * 'dbProfiling'	=> enable profiling of DB queries and param logging.
 *
 * Also there is an additional security feature you may need - 'allowedIPs' option. This option
 * holds the array of IP addresses of all machines you need to use in development cycle. So if you
 * forget to remove YII_DEBUG from bootstrap file for the production stage, your client don't see
 * the toolbar anyway.
 * AllowedIPs be defined as a preg regexps ie: '192\.168\.1[0-5]\.[0-9]{3}'
 */

/**
 * Helper class for array dumping.
 */
class arrayDumper {
	private static $_objects;
	private static $_output;
	private static $_depth;

	/**
	 * Displays a variable.
	 * This method achieves the similar functionality as var_dump and print_r
	 * but is more robust when handling complex objects such as Yii controllers.
	 * @param mixed variable to be dumped
	 * @param integer maximum depth that the dumper should go into the variable. Defaults to 10.
	 * @param boolean whether the result should be syntax-highlighted
	 */
	public static function dump($var, $depth = 10, $highlight = false, $yamlStyle = false) {
		return $yamlStyle == false ? CVarDumper::dumpAsString($var, $depth, $highlight) : self::dumpAsString($var, $depth, $highlight);
	}

	/**
	 * Dumps a variable in terms of a string.
	 * This method achieves the similar functionality as var_dump and print_r
	 * but is more robust when handling complex objects such as Yii controllers.
	 * @param mixed variable to be dumped
	 * @param integer maximum depth that the dumper should go into the variable. Defaults to 10.
	 * @param boolean whether the result should be syntax-highlighted
	 * @return string the string representation of the variable
	 */
	public static function dumpAsString($var, $depth = 10, $highlight = false) {
		self::$_output  = '';
		self::$_objects = array();
		self::$_depth   = $depth;
		
		self::dumpInternal($var, 0);
		$highlight = false;
		if ($highlight) {
			$result        = highlight_string("<?php\n" . self::$_output, true);
			self::$_output = preg_replace('/&lt;\\?php/', '', $result, 1);
		}
		
		return self::$_output;
	}

	private static function dumpInternal($var, $level) {
		switch (gettype($var)) {
			case 'boolean':
				self::$_output .= $var ? 'true' : 'false';
				break;
			case 'integer':
				self::$_output .= "$var";
				break;
			case 'double':
				self::$_output .= "$var";
				break;
			case 'string':
				self::$_output .= "'$var'";
				break;
			case 'resource':
				self::$_output .= '{resource}';
				break;
			case 'NULL':
				self::$_output .= "null";
				break;
			case 'unknown type':
				self::$_output .= '{unknown}';
				break;
			case 'array':
				if (self::$_depth <= $level) {
					self::$_output .= 'array(...)';
				}
				else if (empty($var)) {
					self::$_output .= '{ }';
				}
				else {
					$keys           = array_keys($var);
					$spaces         = str_repeat(' ', $level * 2);
					self::$_output .= $spaces . '';
					
					foreach($keys as $key) {
						self::$_output .= ($level == 0 ? '' : "\n") . $spaces . "  $key: ";
						self::$_output .= self::dumpInternal($var[$key], $level + 1);
						self::$_output .= ($level == 0 ? "\n" : '');
					}
					
					self::$_output .= "";
				}
				break;
			case 'object':
				if (($id = array_search($var, self::$_objects, true)) !== false) {
					self::$_output .= get_class($var) . '#' . ($id + 1) . '(...)';
				}
				else if (self::$_depth <= $level) {
					self::$_output .= get_class($var) . '(...)';
				}
				else {
					$id        = array_push(self::$_objects, $var);
					$className = get_class($var);
					$members   = (array)$var;
					$keys      = array_keys($members);
					$spaces    = str_repeat(' ', $level * 2);
					
					self::$_output .= "$className ID:#$id";//\n".$spaces.'(';
					
					foreach ($keys as $key) {
						$keyDisplay     = strtr(trim($key), array("\0" => '->'));
						self::$_output .= "\n" . $spaces . "  $keyDisplay: ";
						self::$_output .= self::dumpInternal($members[$key], $level + 1);
					}
					
					self::$_output .= "\n" . $spaces . ')';
				}
				
				break;
			default:
				self::$_output .= "\n" . $spaces . '~' . $var;
		}
	}
}

/**
 * Render debug panel to document using view and configuration parameters
 */
class yiiDebugPanel {
	protected static $instance = null;
	protected static $output = null;
	protected static $config = null;
	protected static $items;
	public static $isRendered = false;

	public function setConfig($config) {
		self::$config['alignLeft'] = !empty($config['alignLeft']);
		self::$config['opaque'] = !empty($config['opaque']);
		self::$config['fixedPos'] = !empty($config['fixedPos']);
		self::$config['collapsed'] = !empty($config['collapsed']);
	}

	public function setItems($items) {
		self::$items = $items;
	}

	public function getItems() {
		return self::$items;
	}

	public static function getInstance() {
		if( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	protected function __clone() {}

	protected function __construct() {}

	public static function render() {
		$items = self::$items;
		$config = self::$config;
		
		$msg       = "Run rendering...\n";
		$alignLeft = !empty($config['alignLeft']);
		$opaque    = !empty($config['opaque']);
		$fixedPos  = !empty($config['fixedPos']);
		$collapsed = !empty($config['collapsed']);

		$viewFile  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'debugPanel.php';
		require(Yii::app()->findLocalizedFile($viewFile, 'en'));
	}

}

/**
 * Parent class for the classes may be used on DebugPanel.
 * It have basic grid render and configuration functionality .
 */
class yiiDebugClass {
	protected static $_config = array();

	public static function timestampToTime($timestamp) {
		return date('H:i:s.', $timestamp) . (int)(($timestamp - (int)$timestamp) * 1000000);
	}

	public static function render($items, $config=null) {
		$result = '';
		$odd    = true;
		$categories = array();
		
		foreach ($items as $item) {
			list($message, $level, $category, $timestamp) = $item;
			if( !in_array($category, $categories) )
				$categories[] = $category;
			$message = CHtml::encode($message);
			// put each source file on its own line
			$message = implode("<br/>", explode("\n", $message));
			$time    = yiiDebugTrace::timestampToTime($timestamp);
			$odd     = !$odd;

			$result .= '<tr class="'.($odd ? 'odd '.$level.' '.str_replace('.','_',$category) : $level.' '.str_replace('.', '_', $category)). '"><td>' . $time . '</td><td>' . $level . '</td><td>' . $category . '</td><td>' . $message . '</td></tr>';
		}

		if ($result !== '') {
			$result = '<tbody>' . $result . '</tbody>';
		}

		$category_controls = '';
		foreach($categories as $category) {
			$category_controls .= '<div class="col-xs-2"><input type="checkbox" class="debug_category_trigger" name="'.str_replace('.','_',$category).'" checked="checked">'.$category.'</input></div>';
		}

		$comment = '';
		if(!is_null($config) && isset($config['comment']))
			$comment = '<div class="row">'.$config['comment'].'</div>'.PHP_EOL;
		$result = $comment.$category_controls.PHP_EOL.'<table id="debugInfo"><thead><tr><th>Time</th><th>Level</th><th>Category</th><th width="100%">Message</th></tr></thead>' . $result . '</table>';

		return $result;
	}

	public static function getInfo($data, $config = null) {
		if (!is_null($config)) {
			self::$_config = $config;
		}
	}
}

class yiiDebugDB extends yiiDebugClass {
	static $count = 0;
	static $cached = 0;
	static $items = array();
	static $minimized = false;
	static $information_schema_count = 0;
	static $queryCount = 0;
	static $activeRecordCount = 0;
	static $config = '';
	static $textHighlighter = null;
	static $highlightSql = true;

	public static function getInfo($data, $config = null) {
		parent::getInfo($data);
		
		$result = array();
		$result['panelTitle'] = 'Database Queries';
		if( self::$minimized ) $result['panelTitle'].=' ('.self::$information_schema_count.' information_schema Queries)';
		
		foreach ($data as $row) {
			switch($row[2]) {
				case 'system.db.ar.CActiveRecord' : self::$activeRecordCount++; break;
			}
			if (substr($row[2], 0, 9) == 'system.db') {
				if ($row[2] == 'system.db.CDbCommand') {	
					if (strpos($row[0], 'Querying SQL') !== false) {
						$row[0] = str_replace('Querying SQL:', 'Querying SQL:'.PHP_EOL,$row[0]);
						self::$count++;
					}
					
					if (strpos($row[0], 'Query result found in cache') !== false) {
						self::$cached++;
					}

					if( strpos($row[0], '[INFORMATION_SCHEMA]') !== false ) {
						$row[2] = $row[2].'_information'; // we modify the category
						self::$information_schema_count++;
						// don't log information schema if minimized is enabled
						if( self::$minimized )
							return $result;
					} else {
						// Query Count contains only Application Queries, no DBAL (information_schema)
						self::$queryCount++;
					}
				}
				$row = self::formatLogEntry($row);
				self::$items[] = $row;
			}
		}

		self::$config['comment'] = 'Operations: '.self::$count.' | Application Queries: '.self::$queryCount.' | DBAL Queries: '.self::$information_schema_count.' | ActiveRecords: '.self::$activeRecordCount;

		if (count(self::$items) > 0) {
			$result['content'] = yiiDebugDB::render(self::$items, self::$config);
		}
		
		$result['title'] = 'DB Ops: ' . self::$count;
		
		if (self::$cached > 0) {
			$result['title'] .= " (".self::$cached." cached)";
		}
		
		return $result;
	}

	public static function render($items, $config=null) {
		$result = '';
		$odd    = true;
		$categories = array();
		
		foreach ($items as $item) {
			list($message, $level, $category, $timestamp) = $item;
			if( !in_array($category, $categories) )
				$categories[] = $category;
			//$message = CHtml::encode($message);
			// put each source file on its own line
			$message = implode("<br/>", explode("\n", $message));
			$time    = yiiDebugTrace::timestampToTime($timestamp);
			$odd     = !$odd;

			$result .= '<tr class="'.($odd ? 'odd '.$level.' '.str_replace('.','_',$category) : $level.' '.str_replace('.', '_', $category)). '"><td>' . $time . '</td><td>' . $level . '</td><td>' . $category . '</td><td>' . $message . '</td></tr>';
		}

		if ($result !== '') {
			$result = '<tbody>' . $result . '</tbody>';
		}

		$category_controls = '';
		foreach($categories as $category) {
			$category_controls .= '<div class="col-xs-2"><input type="checkbox" class="debug_category_trigger" name="'.str_replace('.','_',$category).'" checked="checked">'.$category.'</input></div>';
		}

		$comment = '';
		if(!is_null($config) && isset($config['comment']))
			$comment = '<div class="row">'.$config['comment'].'</div>'.PHP_EOL;
		$result = $comment.$category_controls.PHP_EOL.'<table id="debugInfo"><thead><tr><th>Time</th><th>Level</th><th>Category</th><th width="100%">Message</th></tr></thead>' . $result . '</table>';

		return $result;
	}

	/**
	 * Format log entry
	 *
	 * @param array $entry
	 * @return array
	 */
    public static function formatLogEntry(array $entry)
    {
        // extract query from the entry
        $queryString = $entry[0];
		$sqlStart = strpos($queryString, "Querying SQL:");
		// if we have no query we return the whole entry as is
		if($sqlStart === FALSE) return $entry;
		$sqlStart += 14;
		$sqlEnd = strpos($queryString , "\nin D:");
		//$sqlEnd = strlen($queryString);
        $sqlLength = $sqlEnd - $sqlStart;
		
		$beforeQuery = substr($queryString, 0, $sqlStart);
		$afterQuery = substr($queryString, $sqlEnd);
        $queryString = substr($queryString, $sqlStart, $sqlLength);

        if (false !== strpos($queryString, '. Bound with '))
        {
            list($query, $params) = explode('. Bound with ', $queryString);

	        $binds  = array();
	        $matchResult = preg_match_all("/(?<key>[a-z0-9\.\_\-\:]+)=(?<value>[\d\.e\-\+]+|''|'.+?(?<!\\\)')/ims", $params, $paramsMatched, PREG_SET_ORDER);

            if ($matchResult) {
                foreach ($paramsMatched as $paramsMatch)
	                if (isset($paramsMatch['key'], $paramsMatch['value']))                        
                        $binds[':' . trim($paramsMatch['key'],': ')] = trim($paramsMatch['value']);
            }
            $entry[0] = strtr($query, $binds);
        }
        else
        {
            $entry[0] = $queryString;
        }

        if(false !== CPropertyValue::ensureBoolean(self::$highlightSql))
        {
			$entry[0] = $beforeQuery.self::getTextHighlighter()->highlight($entry[0]);
			$entry[0] = $entry[0].$afterQuery;
			$entry[0] = $entry[0].PHP_EOL.'Origin: '.str_replace('Bound with',PHP_EOL.'Bound with',$queryString);
        }

        $entry[0] = strip_tags($entry[0], '<div>,<span>');
        return $entry;
    }

    /**
     * @return CTextHighlighter the text highlighter
     */
    private static function getTextHighlighter()
    {
        if (null === self::$textHighlighter)
        {
            self::$textHighlighter = Yii::createComponent(array(
                'class' => 'CTextHighlighter',
                'language' => 'sql',
                'showLineNumbers' => false,
            ));
        }
        return self::$textHighlighter;
    }

}

class yiiDebugTrace extends yiiDebugClass {
	static $items = array();

	public static function getInfo($data, $config = null) {
		parent::getInfo($data);
		
		$result               = array();
		$result['title']      = 'App Log';
		$result['panelTitle'] = 'Application Log';
		
		foreach ($data as $row) {
			if (substr($row[2], 0, 9) != 'system.db')
			self::$items[] = $row;
		}

		if (count(self::$items) > 0) {
			$result['content'] = yiiDebugTrace::render(self::$items);
		}

		return $result;
	}
}
class yiiDebugTime extends yiiDebugClass {
	public static function getInfo($data, $config = null) {
		parent::getInfo($data);
		
		$result          = array();
		$result['title'] = 'Time: ' . (round(Yii::getLogger()->getExecutionTime(), 3));

		return $result;
	}
}

class yiiDebugMem extends yiiDebugClass {
	public static function getInfo($data, $config = null) {
		parent::getInfo($data);

		$result          = array();
		if ( Yii::app()->format && get_class(Yii::app()->format) == 'CFormatter' ) {
			$size = Yii::app()->format->formatSize(Yii::getLogger()->getMemoryUsage());
		} else {
			//round it for two digits after point
			$size = round(Yii::getLogger()->getMemoryUsage() / 1024, 2) . 'Kb';
		}
		$result['title'] = 'Memory: ' . $size;

		return $result;
	}
}
class yiiDebugConfig extends yiiDebugClass {
	public static $yamlStyle = false;

	public static function getHeadInfo() {
		$result = '';

		$config = array(
			'debug'        => (DEFINED('YII_DEBUG') && YII_DEBUG != false) ? 'on' : 'off',
			'xdebug'       => extension_loaded('xdebug') ? 'on' : 'off',
			'tokenizer'    => function_exists('token_get_all') ? 'on' : 'off',
			'eaccelerator' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') ? 'on' : 'off',
			'apc'          => extension_loaded('apc') && ini_get('apc.enabled') ? 'on' : 'off',
			'xcache'       => extension_loaded('xcache') && ini_get('xcache.cacher') ? 'on' : 'off',
		);

		$result = '<ul class="yiiDebugInfoInline">';
		foreach ($config as $key => $value) {
			$result .= '<li class="is' . $value . '">' . $key . '</li>';
		}
		$result .= '</ul>';

		return $result;
	}

	public static function sessionAsArray() {
		if (isset($_SESSION)) {
			$phpSession = array();
			$sessKeyLen = null;
			
			foreach ($_SESSION as $key => $value) {
				if (is_null($sessKeyLen)) {
					$values['PHP']['Key'] = substr($key, 1, strpos($key, '_') - 1);
					$sessKeyLen           = strlen($values['PHP']['Key']) + 1;
				}
				
				$phpSession[substr($key, $sessKeyLen)] = $value;
			}
			
			$values['PHP']['Data'] = $phpSession;
		}
		
		if (isset($_COOKIE)) {
			$values['Cookie'] = $_COOKIE;
		}
		
		$values['Yii'] = Yii::app()->session;
		
		return $values;
	}

	public static function globalsAsArray() {
		$values = array();
		
		foreach (array('server', 'files', 'env') as $name) {
			if (!isset($GLOBALS['_' . strtoupper($name)])) {
				continue;
			}

			$values[$name] = array();
			
			foreach ($GLOBALS['_' . strtoupper($name)] as $key => $value) {
				$values[$name][$key] = $value;
			}
			
			ksort($values[$name]);
		}
		
		ksort($values);
		
		return $values;
	}

	public static function phpInfoAsArray() {
		$values = array(
			'php'        => phpversion(),
			'os'         => php_uname(),
			'extensions' => get_loaded_extensions(),
		);

		// assign extension version
		if ($values['extensions']) {
			foreach($values['extensions'] as $key => $extension) {
				$values['extensions'][$key] = phpversion($extension) ? sprintf('%s (%s)', $extension, phpversion($extension)) : $extension;
			}
		}

		return $values;
	}

	public static function requestAsArray() {
		$values = array();
		
		if (isset($_GET)) {
			$values['Get'] = $_GET;
		}
		
		if (isset($_POST)) {
			$values['Post'] = $_POST;
		}
		
		$values['Yii'] = Yii::app()->request;
		
		return $values;
	}

	public static function yiiAppAsArray() {
		$result = Yii::app();
		return $result;
	}

	protected static function formatArrayAsHtml($id, $values, $highlight = false) {
		$id = ucfirst(strtolower($id));

		return '
		<div style="text-align: left" class="yiiDebugInfoList">
		<h2> <a href="#" onclick="yiiWebDebugToggleVisible(\'yiiWDCFG' . $id . '\'); return false;">+</a>' . $id . '</h2>'.
		//'<div id="yiiWDCFG'.$id.'" style="display: none;"><pre>' .($formatted ? $values : arrayDumper::dump(arrayDumper::removeObjects($values),$highlight)) . '</pre></div></div>';
		'<div id="yiiWDCFG' . $id . '" style="display: none;"><pre>' . arrayDumper::dump($values, 10, $highlight, !empty(self::$_config['yamlStyle'])) . '</pre></div></div>';
	}

	public static function getInfo($data, $config = null) {
		parent::getInfo($data, $config);
		
		$result               = array();
		$result['title']      = 'Yii ver: '.(Yii::getVersion());
		$result['headinfo']   = self::getHeadInfo();
		$result['panelTitle'] = 'Configuration';
		$result['content']    = self::formatArrayAsHtml('globals', self::globalsAsArray(), true);
		$result['content']   .= self::formatArrayAsHtml('session', self::sessionAsArray(), true);
		$result['content']   .= self::formatArrayAsHtml('php', self::phpInfoAsArray(), true);
		$result['content']   .= self::formatArrayAsHtml('request', self::requestAsArray(), true);
		$result['content']   .= self::formatArrayAsHtml('Yii::app()', self::YiiAppAsArray() , true);
		$result['content']   .= self::formatArrayAsHtml('WebdebugToolbar', self::$_config, true);

		return $result;
	}
}

/**
 * Main class for using inside an Yii application
 *
 * It processes the logs of running instance of application
 * and renders self output to the end of server output (after </html> tag of document).
 */
class XWebDebugRouter extends CLogRoute {
	private $isRegistered = false;
	public $allowedIPs = array('127.0.0.1', '::1'); // IPv4 and IPv6 localhost addresses

	private $_config   = array(
		'alignLeft'   => false, //debug toolbar will be aligned to the top left corner of browser window
		'opaque'      => false, //makes debug toolbar almost invisible when it’s minimized
		'runInDebug'  => false, //show debug toolbar only if Yii application running in DEBUG MODE (see index.php for details)
		'fixedPos'    => false, //makes debug toolbar sticky with browser window, not document!
		'collapsed'   => false, //show debug toolbar minimized by default
		'yamlStyle'   => false, //show configuration report in Yaml or PHP-array style.
		'dbProfiling' => false, //enable profiling of DB queries and param logging
	);

	public function init() {
		parent::init();
		if( $this->_isLoggerAllowed() ) {
			if ( !empty($this->_config['dbProfiling']) )  {
				Yii::app()->db->enableProfiling = true;
				Yii::app()->db->enableParamLogging = true;
			}
			$panel = yiiDebugPanel::getInstance();
			$panel->setConfig($this->_config);
		}
	}

	public function collectLogs($logger, $processLogs = false) {
		$logs = $logger->getLogs($this->levels, $this->categories);

		if (empty($logs)) {
			$logs = array();
		}

		$this->processLogs($logs);
	}

	public function processLogs($logs) {
		if (!$this->_isLoggerAllowed())
			return;

		$items = array();

		$items[] = yiiDebugConfig::getInfo($logs, $this->_config);
		$items[] = yiiDebugMem::getInfo($logs);
		$items[] = yiiDebugTime::getInfo($logs);
		$items[] = yiiDebugDB::getInfo($logs);
		$items[] = yiiDebugTrace::getInfo($logs);

		// new code
		$panel = yiiDebugPanel::getInstance();
		$panel->setConfig($this->_config);

		$panel->setItems($items);
		
		// If any Logger sets autoFlush to 1 (like PHPConsole) we don't want to render here, instead we wait for onEndRequest
		// actually we need to find out if we render more than once - onEndRequest would be great actually
		// probably another way would be to see if log count is same as autoflush
		$autoFlush = Yii::getLogger()->autoFlush;
		if( $autoFlush == 1 ) {
			// we only want to register the event if it's not done already
			if(!($this->isRegistered)) {
				// this event is not raised, without phpconsole (or autoflush before end) - doesn't make any sense
				// even registering it in init doesn't work
				Yii::app()->onEndRequest = array('YiiDebugPanel', 'render');
				$this->isRegistered = true;
			}
		} else {
			// without php console we have to render here, as processlogs happens after onEndRequest
			// problem - with php console it renders here twice (on first entry and later on endrequest event)
			$panel->render($items, $this->_config);
		}
		// end new code
	}

	public function setConfig($config) {
		if (is_array($config)) {
			$this->_config = $config;
		} else {
			foreach (explode(',', $config) as $value) {
				$value = trim($value);
				$this->_config[$value] = true;
			}
		}
	}

	private function _isLoggerAllowed() {
		$app = Yii::app();

		//Checking for an DEBUG mode of running app
		if (!empty($this->_config['runInDebug']) && (!DEFINED('YII_DEBUG') || YII_DEBUG == false))
			return false;

		//Checking for an AJAX Requests
		if (!($app instanceof CWebApplication) || $app->getRequest()->getIsAjaxRequest())
			return false;

		//Checking IP
		$ip = $app->request->getUserHostAddress();
		foreach ($this->allowedIPs as $pattern) {
			// if found any char other than [0-9] and dot, treat pattern as a regexp
			if (preg_match('/[^0-9:\.]/', $pattern)) {
				if (preg_match('/' . $pattern . '/', $ip)) {
					return true;
				}
			}
			else if ($pattern === $ip) {
				return true;
			}
		}

		return false;
	}
}
