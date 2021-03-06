<?php
/*------------------------------------------------------------------------------
  SMPL Version:   0.1.0
  Issue Date:     August 1, 2013
  Copyright (c):  2013 Gowon Patterson, Gowon Designs
  License:        This program is distributed under the terms of the
                  GNU General Public License v3
                  <http://www.gnu.org/licenses/gpl-3.0.html>
------------------------------------------------------------------------------*/
Debug::Set(Debug::DEBUG_ON, Debug::STRICT_OFF, Debug::VERBOSE_OFF, Debug::LOGGING_OFF, __DIR__);

IncludeFromFolder("classes/");


// Include all of the classes located in the classes/ folder
function IncludeFromFolder($folder)
{
    foreach (glob("{$folder}*.php") as $filename)
        require_once($filename);
}

function __autoload($class_name)
{
    include_once('classes/Class.'.$class_name.'.php');
} 
//////////////////////////////////////////////////////////////
// Always update the database, un/publishing content based on the current date

Security::EnforceHttps();

Security::Authenticate();

Content::Initialize();

Content::Hook();



?>