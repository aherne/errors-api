# MVC Errors API

This is a revolutionary API, part of [Lucinda Framework 2.0](http://www.lucinda-framework.com), created to elegantly and efficiently [handle errors/exceptions](http://www.lucinda-framework.com/blog/how-does-php-handle-errors) in an application into log reports and server responses using a dialect of MVC paradigm. 

More specifically, a STDERR micro-framework that feeds on errors (exceptions) instead of web requests, able to provide a powerful unified error handling strategy that works on MVC principles. Once an error has occurred in STDOUT flow (as a request is handled to produce a response), it gets automatically redirected (via standard PHP error redirection functions) to a dormant framework that wakes up when getting an error to handle.

Read more here:<br/>
http://www.lucinda-framework.com/stderr-mvc