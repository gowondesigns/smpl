<?php
/* SMPL Sitemap Class
// 
// Example Use:
// $l = LanguageFactory::Create("en-US");
// echo $l->Phrase("Author");
//
//*/


static class Sitemap
{

    public static function CreateXML()
    {
        header("Content-Type: application/xml charset=utf-8");
        $database = Database::Connect();
        
        $xml = "<\x3F".'xml version="1.0" encoding="utf-8'."\x3F>\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $result = $database->Retrieve('categories', 'title_mung-field', "publish_flag-checkbox = 1");
        while($category = $result->fetch_array(MYSQLI_ASSOC))
        {
            $xml .= "\n\t<url>";
            $xml .= "\n\t\t<loc>".Utils::GenerateUri('categories',$category['title_mung-field']).'</loc>';
            $xml .= "\n\t</url>\n";
        }
        
        $result = $database->Retrieve('content', '*', "content-static_page_flag-checkbox = 1 AND publish-publish_flag-dropdown = 2");
        while($pages = $result->fetch_array(MYSQLI_ASSOC))
        {
            $xml .= "\n\t<url>";
            $xml .= "\n\t\t<loc>".Utils::GenerateUri($pages['content-title_mung-field']).'</loc>';
            $xml .= "\n\t\t<lastmod>".Date::CreateFlat(Date::Create($pages['content-date-date']), "Y-m-d\x54H:i:sP").'</lastmod>';b
            $xml .= "\n\t</url>\n";
        }
        
        $xml .= "\n</urlset>";
        
        echo $xml;
    }
}

?>