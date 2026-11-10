# \RosarioSIS\Functions classes

Functions from the `ProgramFunctions/` folder are progressively moved here.

This is meant to avoid having to require files in the `ProgramFunctions/` folder,
and benefit from the autoloading capability.

## Instead of

```php
require_once 'ProgramFunctions/SendEmail.fnc.php';

$sent = SendEmail( $arguments... );
```

## Write

```php
$sent = (new RosarioSIS\Functions\Email)->send( $arguments... );
```
