<?php
/* SMPL Form Classes
// 
//
//*/


static class Forms
{
    private static $forms = array();
    

    public static function Create($name)
    {
    
        if (isset($this->forms[$name]))
            $form = $this->forms[$name];
        else
        {
            $form = new Form();
            $this->forms[$name] = $form;
        }

        return $form;
    }

    public static function Info($name = null)
    {
        if (null === $name)
        {
            return $this->forms;
        }
        
        return $this->forms[$name];
    }

    // Assuming data is coming from GET or POST
    public static function Validate($formName, $validationScheme)
    {
        if (null === $name)
        {
            return $this->forms;
        }
        
        return $this->forms[$name];
    }
    
    // May not be necessary
    public static function CreatePanelElement($panelItem)
    {
        $l = LanguageFactory::Create();
        $panel = explode( ',', $panelItem); 
        
        switch (count($panel)) {
            case 3:
                
            case 2:
            case 1:
            default:
                $newAttributes = array(
                    'maxlength',
                    'size'
                    );
                $this->allowedAttributes = array_merge($this->allowedAttributes, $newAttributes);
                break;
        }
        
        $l->Phrase("Author");
        
        
        $element = new Form();
        
        if (null === $name)
        {
            $this->forms[] = $form;
        }
        else
        {
            $this->forms[$name] = $form;
        }

        return $element;
    }
}


class Form
{
    private $name;
    private $id;
    private $action;
    private $method;
    private $enctype;
    private $errors;
    private $elements = array();
    private $attributes = null;
    private $allowedAttributes = array(
        'class',
        'contextmenu',
        'dir',
        'draggable',
        'dropzone',
        'id',
        'spellcheck',
        'style',
        'tabindex',
        'title',
        'maxlength',
        'size',
        'autocomplete',
        'list',
        'pattern',
        'placeholder',
        'target'
    );
    private $booleanAttributes = array(
        'novalidate'  
    );            
    
    public function __construct($name, $id, $action, $method='post', $enctype='application/x-www-form-urlencoded')
    {


    }

    // AddElement($name, $element)
    // AddElement($group, $name, $element)
    public function AddElement($stub)
    {

    }
    
    // AddElement($name)
    // AddElement($group, $name)
    public function RemoveElement($stub)
    {

    }

    public function GetElements()
    {
        return $this->elements;
    }
    
    public SetAttributes($attributes)
    {
        if (!is_array($attributes))
            return;
            
        foreach ($attributes as $key => $value)
        {
            if (null === $value)
                unset($this->attributes[$key]);
            else
                $this->attributes[$key] = htmlentities($value);
        }
    
    }
    
    protected ValidateAttributes()
    {
        $attributes = null;
        
        foreach( $this->attributes as $key => $value )
        {
            if ( in_array($key, $this->allowedAttributes) )
                $attributes .= " $key=\"$value\"";
                    
            if ( in_array($key, $this->booleanAttributes) )
                $attributes .= " $key=\"$key\"";
        }
        
        return $attributes;
    }
    
    public Execute($postData = null)
    {
        // Create Session Variable for Initiation and Validation
        // Output HTML 
    } 

}

interface iFormElement
{
    public Html();
    public Enable();
    public Disable();
    public IsEnabled();
    public Validate($data);
}

abstract class aFormElement
{
    protected $name;
    protected $id;
    protected $type;
    protected $value = null;
    protected $content = null;
    protected $attributes = null;
    protected $isEnabled = true;

    // List of acceptable optional attributes   
    protected $allowedAttributes = array(
        'class',
        'contextmenu',
        'dir',
        'draggable',
        'dropzone',
        'id',
        'spellcheck',
        'style',
        'tabindex',
        'title',
        'autocomplete',
        'list',
        'pattern',
        'placeholder'
    );

    // List of acceptable optional boolean attributes     
    protected $booleanAttributes = array(
        'disabled',
        'readonly',
        'autofocus',
        'required'    
    );
    
    public __construct($name, $id, $type)
    {
        $this->name = $name;
        $this->id = $name;
        $this->type = $name;
    }
    
    public Enable()
    {
        $this->isEnabled = true;
    }

    public Disable()
    {
        $this->isEnabled = false;
    }
    
    public IsEnabled()
    {
        return $this->isEnabled;
    }
    
    public SetValue($value)
    {
        $this->value = htmlentities($value);
    }

    public SetContent($content)
    {
        $this->content = $content;
    }
        
    // Input must be an array
    public SetAttributes($attributes)
    {
        if (!is_array($attributes))
            return;
            
        foreach ($attributes as $key => $value)
        {
            if (null === $value)
                unset($this->attributes[$key]);
            else
                $this->attributes[$key] = htmlentities($value);
        }
    
    }
    
    // Garbage or nonstandard elements are ignored
    protected ValidateAttributes()
    {
        $attributes = null;
        
        foreach( $this->attributes as $key => $value )
        {
            if ( in_array($key, $this->allowedAttributes) )
                $attributes .= " $key=\"$value\"";
                    
            if ( in_array($key, $this->booleanAttributes) )
                $attributes .= " $key=\"$key\"";
        }
        
        return $attributes;
    }
}

class ButtonElement extends aFormElement implements iFormElement
{   
    public __construct($name, $type = 'button')
    {
        parent::__construct($name, $name, $type);
    }
    
    public Html()
    {
        $html = '<button type="'.$this->type.'" name="'.$this->name.'" id="'.$this->id.'"';
        
        if (isset($this->value))
            $html .= ' value="'.$this->value.'"';
        
        if (isset($this->attributes))
            $html .= ValidateAttributes();
            
        $html .= '>'.$this->content."</button>\n\n";
        
        return $html;
    }
    
    // Buttons do not have any editable data to validate
    public Validate($data)
    {
        return true;
    }
}

class InputElement extends aFormElement implements iFormElement
{
    public __construct($name, $id, $type)
    {
        parent::__construct($name, $id, $type);

        switch ($type) {
            case 'checkbox':
                $newAttributes = array(
                    'checked'
                    );
                $this->$booleanAttributes = array_merge($this->$booleanAttributes, $newAttributes);
                break;
            case 'number':
                $newAttributes = array(
                    'min',
                    'max',
                    'step'
                    );
                $this->allowedAttributes = array_merge($this->allowedAttributes, $newAttributes);
                break;
            case 'url':
            case 'text':
            case 'password':
                $newAttributes = array(
                    'maxlength',
                    'size'
                    );
                $this->allowedAttributes = array_merge($this->allowedAttributes, $newAttributes);
                break;
            case 'hidden':
            default:
                break;
        }


    }
    
    public Html()
    {
        if ($this->type == 'checkbox')
            $html = '<label for="'.$this->id.'">'.$this->content.'</label>';
        else
            $html = '<fieldset><label for="'.$this->id."\">\n\t<span>".$this->content."</span>\n\t";
        
        $html .= '<input type="'.$this->type.'" name="'.$this->name.'" id="'.$this->id.'"';
        
        if (isset($this->value))
            $html .= ' value="'.$this->value.'"';
        
        if (isset($this->attributes))
            $html .= ValidateAttributes();
        
        if ($this->type == 'checkbox')
            $html .= " />\n</label></fieldset>\n\n";
        else
            $html .= " />\n\n";
        
        return $html;
    }
    
    // Buttons do not have any editable data to validate
    public Validate($data)
    {
        $valid = true;
        
        switch ($type) {
            case 'text':
            case 'password':
            case 'checkbox':
            case 'hidden':
            case 'number':
            case 'email':
            case 'url':
                $newAttributes = array();
                $this->allowedAttributes = array_merge($this->allowedAttributes, $newAttributes);
                break;
            default:
                $newAttributes = array(
                    'maxlength',
                    'size'
                    );
                $this->allowedAttributes = array_merge($this->allowedAttributes, $newAttributes);
                break;
        }
        
        return $valid;
    }
}

?>
