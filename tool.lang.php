<?php
define("DEBUG_MODE", true);
define("DEBUG_STRICT", false);
define("DEBUG_VERBOSE", true);
define("DEBUG_LOGGING", false);


error_reporting(-1);
set_error_handler(array('Debug', 'ErrorHandler'));
register_shutdown_function(array('Debug', 'EndOfExecution'));
Debug::Set(DEBUG_MODE, DEBUG_STRICT, DEBUG_VERBOSE, DEBUG_LOGGING);

function __autoload($class_name)
{
    require_once('classes/Class.'.$class_name.'.php');
}

IncludeFromFolder("classes/");



$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SMPL Language Tool</title>
</head>

<body id="home">

    <h1>SMPL Language Tool</h1>
    <form action="?" method="post">
    </form>';

$html .= "<h2></h2>
</body>
</html>";

echo $html;
/*
$database = Config::Database();
$database->Queries();
var_dump(get_class_methods($database));
*/

function IncludeFromFolder($folder)
{
    foreach (glob("{$folder}*.php") as $filename)
    {
        //print('Including '.$filename.'<br>');
        require_once($filename);
    }
}

?>
