<?php
define("DEBUG_MODE", true);
define("DEBUG_STRICT", false);
define("DEBUG_VERBOSE", false);
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
    <title>SMPL API Tool</title>
</head>

<body id="home">

    <h1>SMPL API Tool</h1>
    <form action="?" method="post">
          <label for="http-path">Target URI:</label>
          <input type="text" value="'.$_POST['http-path'].'" id="http-path" name="http-path" /><br/>
          <label for="http-header-text">HTTP Header information:</label>
          <textarea id="http-header-text" name="http-header-text" style="width: 500px; height: 125px">'.$_POST['http-header-text'].'</textarea><br/> 
          <button type="submit" name="testHttp">Test Request</button>
    </form>';

$statusMessage = null;
if (isset($_POST['testHttp'])) $statusMessage = TestHttpRequest($_POST['http-path'], $_POST['http-header-text']);
//if (isset($_POST)) $statusMessage = print_r($_POST, true);
//if (isset($_POST)) $statusMessage .= print_r(explode("\n", $_POST['http-header-text']), true);

$html .= $statusMessage
.'</body>
</html>';

echo $html;


function IncludeFromFolder($folder)
{
    foreach (glob("{$folder}*.php") as $filename)
    {
        //print('Including '.$filename.'<br>');
        require_once($filename);
    }
}

function TestHttpRequest($uri, $httpRequest)
{    
    $ch = curl_init($uri);
    $header = explode("\n", $httpRequest);
    
    $msg = "<pre>HTTP Request:\n";
    foreach($header as $line)
        $msg .= $line."\n";

    //curl_setopt($ch, CURLOPT_URL, $uri);  
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
    curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
    curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

  
    $output = curl_exec($ch);
    if($output === false)
    {
        echo 'Curl error: ' . curl_error($ch);
    }
    
    $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $msg .= "</pre><pre style=\"color: #fff; background-color: #000;\">\n\nHTTP Response:\n".$output.'</pre>';
    return $msg;
}

?>
