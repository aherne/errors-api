# MVC Errors API

STDERR micro-framework that feeds on errors (exceptions) instead of web requests, able to provide a powerful unified error handling strategy that works on MVC principles. Once an error has occurred in STDOUT flow (as a request is handled to produce a response), it gets automatically redirected (via standard PHP error redirection functions) to a dormant framework that wakes up when getting an error to handle. 

##Initialization

In order to take advantage of this software and make sure it handles all errors, your application bootstrap must start with:

```php
require_once("vendor/lucinda/errors-mvc/src/FrontController.php");
new Lucinda\MVC\STDERR\FrontController(XML_FILE, DEVELOPMENT_ENVIRONMENT);
```

This loads STDERR framework then starts its front controller who registers itself as sole error handler that will from now on catch any php error (notices, warnings, fatal) or uncaught exceptions. As one can notice above, starting front controller requires two arguments:

- location to XML file that stores application settings. To develop one, take *vendor/lucinda/errors-mvc/src/configuration.xml* as an example.
- name of development environment (eg: local, dev, live), necessary to establish different strategies of error reporting based on that environment

Once framework initializes, it lies dormant waiting for error requests to handle.

##Handling

Once an error has occurred in your application, framework's FrontController *handle* method will be automatically called to handle it. Handling process involves following steps:

- loading framework dependencies (for performance reasons, they were not loaded in initialization stage)
- parsing XML file that stores application settings into an Application object
- reading the error request (which takes the shape of an exception, since PHP errors themselves were redirected to exceptions in initialization stage) into a Request object
- producing a Response object that encapsulates answer back to caller
- locating and executing a Controller that handles that Exception (necessary when developer wants, for maximum flexibility, to fine tune handling process on exception level). This step only occurs if developer has defined a *controller* attribute in &lt;exceptions&gt; tag or &lt;exception&gt; tag whose *class* attribute matches Exception class.
- perform error handling itself, which involves two processes:
	- **reporting**: This means saving error to one or more storage mediums (eg: files, database). Framework starts all reporters defined as *class* in &lt;reporter&gt; tag (detected in &lt;reporters&gt; tag based on development environment) then calls their *report* method (by virtue of all reporters implementing ErrorReporter interface) to deal with Request above.
	- **rendering**: This means showing an answer back to caller after an error has occured in your application. Framework starts renderer defined as *class* in &lt;renderer&gt; tag (detected in &lt;renderers&gt; tag based on application-default content type defined in XML as &lt;default_content_type&gt; or manually overridden via **setContentType** FrontController method during STDOUT process) then calls its *render* method (by virtue of all renderers implementing ErrorRenderer interface) to deal with Response above.
	
If an error has occurred in handling process, which can only happen if XML was badly configured, an Lucinda\MVC\STDERR\Exception is thrown and framework exits with error message.

## Advantages

Because STDOUT and STDERR MVC frameworks are completely independent with no shared dependencies, they are able to work independently or in tandem without any clashing. Developers are only expected to:

- write XML file that stores application settings on  *vendor/lucinda/errors-mvc/src/configuration.xml* model
- implement Controller instances then hook them in XML (see above for details). This step is to be done only if developer desires to handle exceptions different from default! 
- implement one or more ErrorReporter instances then hook them in XML (see above for details). This step is to be done only if developer wants to save error details to a storage medium (which is normally the case)!  
- implement one or more ErrorRenderer instances that matches your application content types then hook them in XML (see above for details). This step is MANDATORY!