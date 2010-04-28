# Referee Plugin for CakePHP 1.3+

Referee plugin catches errors and exceptions and logs them to the database.

> "*If you're waiting around for users to tell you about problems with your website or application, you're only seeing a tiny fraction of all the problems that are actually occurring. The proverbial tip of the iceberg.*

> *Also, if this is the case, I'm sorry to be the one to have to tell you this, but you kind of suck at your job -- **which is to know more about your application's health than your users do**.*" - [Exception Driven Development][1]

## Features
 * Easily extended
 * Logs all (*even fatal*) errors and exceptions
 * Monitor errors in real time

## Installation

* Download the plugin

        $ cd /path/to/your/app/plugins && git clone git://github.com/joebeeson/referee.git

* Create the schema

        $ cake schema create Referee.schema

* Add the component to the top of your `AppController`

        public $components = array('Referee.Whistle');

## Extend

You can extend the component to add in custom "listeners" to perform actions when specific errors occur. This is useful for notifications, opening error tickets, etc.

* Create a PHP file in the `/your/app/plugins/referee/vendors/listeners` directory

        $referee = ClassRegistry::getObject('Referee.Whistle');
        $referee->attach(E_ERROR, 'sendEmailFunction');

The `sendEmailFunction` will be executed when a `E_ERROR` occurs and will be passed a handful of parameters about the error. 

## Monitor

Included is a shell for monitoring production applications in realtime

* Fire up a terminal

       $ cake errors monitor
  [1]: http://www.codinghorror.com/blog/2009/04/exception-driven-development.html
