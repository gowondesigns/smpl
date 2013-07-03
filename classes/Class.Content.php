<?php
/* SMPL Content Class
// 
// Example Use:
// $l = LanguageFactory::Create("en-US");
// echo $l->Phrase("Author");
//
//*/


static class Content
{
    private static $spaces = array('main' => null);

    
    public static function Status()
    {
        return print_r($this->spaces, true);        
    }
    
    public static function Space($spaceName)
    {
        if (!isset($this->spaces[$spaceName]) || null === $this->spaces[$spaceName])
        {
            $this->spaces[$spaceName] = new Space($spaceName);
        }

        return $this->spaces[$spaceName];
    }
    
    public static function HtmlHeader()
    {

    }
    
    
    public static function AdminBlock()
    {
        if (true) // If the user is logged in
        {
            $site = Configuration::Site();
            $html = null;
            $l = LanguageFactory::Create(); // Grab language data

            $html .= '<div class="block_admin"><ul>';
            $html .= '<li><a href="'.Utils::GenerateUri($l->Phrase('admin')).'" title="'.$l->Phrase('Admnistration').'">'.$l->Phrase('Admnistration').'</a></li>';
            if (true) // If the user has permissions to edit the current content piece
            {
                $html .= '<li><a href="'.Utils::GenerateUri($l->Phrase('admin'),'CONTENT-TYPE','edit','CONTENT-ID').'" title="'.$l->Phrase('Admnistration').'">'.$l->Phrase('EditContent').'</a></li>';
            }
            $html .= '<li><a href="'.Utils::GenerateUri($l->Phrase('admin')).'" title="'.$l->Phrase('Logout').'">'.$l->Phrase('Logout').'</a></li>';
            $html .= '</ul></div>';
            
            echo $html;
        }
    }

    public static function Breadcrumbs($seperator = "&bull;")
    {
        // Grab and explode URI Query data
        // List assets in heirarchical order, with links to various paths
    }
    
    
}

// Sttucture for all content elements
abstract class aContentObject
{
}

class Page extends aContentObject
{
}

class Article extends aContentObject
{
}

class Block extends aContentObject
{
}

?>
