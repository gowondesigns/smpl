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
    private static $langInstance = null;
    
    
    public static function Space($spaceName)
    {
        $l = LanguageFactory::Create();
        echo $l->Phrase("Author");
        
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


?>
