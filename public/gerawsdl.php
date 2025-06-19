<?php
require_once '/vendor/autoload.php'; 

use wsdl2phpgenerator\WSDL2PHPGENERATOR;

$wsdl2php = new WSDL2PHPGENERATOR('https://localhost/wsceqweb/');
var_dump($wsdl2php);
$wsdl2php->generateCode('app/wsdl/wsceqweb.php');
