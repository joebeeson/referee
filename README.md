# Referee Plugin for CakePHP 1.3+

Referee plugin catches errors and dispatches them to custom listeners.

## Installation

* Download the plugin

        $ cd /path/to/your/app/plugins && git clone git://github.com/joebeeson/referee.git

* Create the schema

        $ cake schema create Referee.schema

* Add the component and attach your listeners

        public $components = array(
            'Referee.Whistle' => array(
                'listeners' => array(
                    'DbLog',
                    'SysLog'
                )
            )
        );

## Listeners

Listeners perform the actual leg work in handling an error. Their only requirement is that they have a public function that the `WhistleComponent` can trigger when it needs to notify the listener of an error.

You can configure listeners in the `$components` declaration for the `WhistleComponent`, here's an example of some configuration options...

        public $components = array(
            'Referee.Whistle' => array(
                'listeners' => array(

                    'YourLogger' => array(

                        // The error type(s) that should trigger this listener. Defaults to E_ALL
                        'levels' => E_ERROR,

                        // The method to call to pass an error, defaults to 'error'
                        'method' => 'customMethod',

                        // The class to instantiate, defaults to (name)Listener, YourLoggerListener in this case
                        'class'  => 'yourCustomListenerClass',

                        'parameters' => array(
                            /**
                             * Anything in here will be passed to the listener's
                             * error method when an error occurs.
                             */
                        )

                    )

                )
            )
        );

If you'd like to instantiate multilpe instances of the `YourLogger` listener, in this instance, simply nest multiple arrays, one for each instantiation, with the configuration for each specific instance -- [similar to how you do multiple validations on models][2].

You can also attach listeners using the `attachListener` method. It will return a boolean to indicate success in attaching the listener.

        $this->Whistle->attachListener(
            'YourLogger',
            array(
                'levels' => E_ERROR,
                'method' => 'customMethod',
                'class'  => 'yourCustomListenerClass',
                'parameters' => array(
                    // Optional parameters to pass
                )
            )
        );

The method that is invoked by the `WhistleComponent` should accept two parameters: `$error` and `$parameters` -- the `$error` is an associative array describing the error that occurred and `$parameters` is an array containing the parameters (*if any*) that were declared in the `$components` declaration for the listener.

## Notes

Previous versions of the plugin handled the recording of all errors to the database, there is no longer such automatic functionality. If you'd like something similar there is a `DbLog` listener available.

  [1]: http://www.codinghorror.com/blog/2009/04/exception-driven-development.html
  [2]: http://book.cakephp.org/view/133/Multiple-Rules-per-Field
