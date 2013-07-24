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

    // Assuming data is coming from GET or POST
    public static function Validate(Form $form, $data)
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
    private $name;
    private $id;
    private $action;
    private $method;
    private $enctype;
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
        'maxlength',
        'size',
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
}

class ButtonElement extends aFormElement implements iFormElement
{
    private $content;
    
    public __construct($name, $type = 'button')
    {
        parent::__construct($name, $name, $type);
    }
    
    public SetContent($content)
    {
        $this->content = $content;
    }
    
    public Html()
    {
        $html = '<button type="'.$this->type.'" name="'.$this->name.'"';
        
        if (isset($this->value))
            $html .= ' value="'.$this->value.'"';
        
        if (isset($this->attributes))
            $html .= ValidateAttributes();
            
        $html .= '>'.$this->content."</button>\n\n";
        
        return $html;
    }
}

?>
