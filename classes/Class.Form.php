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
    private $options = array();
    
    
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

    public function AddElement($stub)
    {

    }
       
    public function RemoveElement($stub)
    {

    }
    
    public function AddOption($name)
    {
    }
    
    public function RemoveOption($name)
    {
    }  

}

interface iFormElement
{
    public Html();
    public Enable();
    public Disable();
    public IsEnabled();
}

abstract class aFormElement
{
    protected $id;
    protected $name;
    protected $value = null;
    protected $extra = null;
    protected $disabled = false;
    
    
    public __construct($name, $id)
    {
        $this->name = $name;
        $this->id = $id;
    }
    
    public Enable()
    {
        $this->disabled = false;
    }

    public Disable()
    {
        $this->disabled = true;
    }
    
    public IsEnabled()
    {
        return $this->disabled;
    }
    
    public SetValue($value)
    {
        $this->value = htmlentities($value);
    }
    
    // Input must be an array
    public SetExtra($extra)
    {
        if (!is_array($extra))
            return false;
            
        foreach ($extra as $key => $value)
        {
            $this->extra[$key] = htmlentities($value);
        }
    
    }
}

class ButtonElement extends aFormElement implements iFormElement
{
    private $type;
    private $content;

    
    public __construct($name, $type = 'button')
    {
        parent::__construct($name, null);
        
        $this->type = $type;
    }
    
    public SetContent($content)
    {
        $this->value = $content;
    }
    
    public Html()
    {
        $html ='<button type="'.$this->type.'" name="'.$this->name.'"';
        
        if (isset($this->value))
            $html .= ' value="'.$this->value.'"';
        
        if (isset($this->extra))
        {
            foreach($this->extra as $key => $value)
            {
                $html .= ' value="'.$this->value.'"';
            }
        }

        
        return $html;
    }
}

?>
