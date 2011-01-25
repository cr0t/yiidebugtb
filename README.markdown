#Yii Debug Toolbar Yii Framework Extension

This extension is very useful in the development stage of project. It helps you
to get info about PHP environment, application, queries logs, benchmarking with
a simple little toolbar at the top of the page.

It is a recreated Symfony's developer toolbar. First idea and very first
implementation was my, but this version mostly implemented by Eduard Kuleshov.

#Usage

##Requirements

* Yii 1.0 or above

##Installation

* Extract the release file under protected/extensions

##Usage

main.php configuration file update:

    [...] 
    // autoloading model and component classes
    'import'=>array(
      'application.models.*',
      'application.components.*',
      'application.extensions.yiidebugtb.*', //our extension
    ),
    [...]
    'log'=>array(
      'class'=>'CLogRouter',
        'routes'=>array(
          array(
            'class'=>'CFileLogRoute',
            'levels'=>'error, warning, trace',
          ),
          array( // configuration for the toolbar
            'class'=>'XWebDebugRouter',
            'config'=>'alignLeft, opaque, runInDebug, fixedPos, collapsed, yamlStyle',
            'levels'=>'error, warning, trace, profile, info',
						'allowedIPs'=>array('127.0.0.1','::1','192.168.1.54','192\.168\.1[0-5]\.[0-9]{3}'),
          ),
        ),
    ),
    [...]

###Options reference

* ‘alignLeft’ => Debug toolbar will be aligned to the top left corner of browser window
* ‘opaque’ => Makes debug toolbar almost invisible when it’s minimized
* ‘runInDebug’ => Show debug toolbar only if Yii application running in DEBUG MODE (see index.php for details)
* ‘fixedPos’ => Makes debug toolbar sticky with browser window, not document!
* ‘collapsed’ => Show debug toolbar minimized by default
* ‘yamlStyle’ => Show configuration report in Yaml or PHP-array style.

Also there is an additional security feature you may need - 'allowedIPs' option.
This option holds the array of IP addresses of all machines you need to use in
development cycle. So if you forget to remove YII_DEBUG from bootstrap file for
the production stage, your client don't see the toolbar anyway.