# Referee Plugin for CakePHP 1.3+

Referee plugin catches errors and exceptions and logs them to the database.

> "*If you're waiting around for users to tell you about problems with your website or application, you're only seeing a tiny fraction of all the problems that are actually occurring. The proverbial tip of the iceberg.*" - [Exception Driven Development][1]

## Features
 * Easily extended
 * Logs all (*even fatal*) errors and exceptions
 * Monitor errors in real time

## Installation

* Download the plugin

        $ cd /path/to/your/app/plugins && git clone git://github.com/joebeeson/referee.git

* Add the component to the top of your `AppController`

        public $components = array('Referee.Whistle');


  [1]: http://www.codinghorror.com/blog/2009/04/exception-driven-development.html