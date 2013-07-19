<?php
/* SMPL Form Classes
// 
//
//*/


static class Forms
{
    private static $forms = array();
    

    public static function Create($name = null)
    {
        $form = new Form();
        
        if (null === $name)
        {
            $this->forms[] = $form;
        }
        else
        {
            $this->forms[$name] = $form;
        }

        return $form;
    }

    public static function Retrieve($name = null)
    {
        if (null === $name)
        {
            return $this->forms;
        }
        
        return $this->forms[$name];
    }
}


class Form
{
    private $elements = array();
    
    
    public function __construct($languageCode)
    {
        // If the language code is 
        if (isset($languageCode))
        {
            include("smpl-languages/lang.".$languageCode.".php");
            if(!isset($SMPL_LANG_DESC) || !isset($SMPL_LANG_CODE) || !isset($SMPL_LANG_PHRASES))
            {
                die('Invalid Language File'); // [MUSTCHANGE]
            }
            
            $this->language = $SMPL_LANG_DESC;
            $this->languageCode = $SMPL_LANG_CODE;
            foreach ($SMPL_LANG_PHRASES as $key => $value)
            {
                $this->Update($key, $value);
            }
            
        }
    }

    // Get the information on the current language    
    public function Info()
    {
        return array(               
            "language" => $this->language,
            "code" => $this->languageCode);
    }

    // Use language phrase    
    public function AddElement($stub)
    {

    }
    
    // Add/Update/Remove the content of a particular phrase, the changes are global    
    public function RemoveElement($stub)
    {

    }  

}

interface iFormElement
{
}

abstract class aFormElement
{
}

class ButtonElement extends aFormElement implements iFormElement
{
}

?>
