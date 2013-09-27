<?php
/* SMPL Sitemap Class
// 
//
//*/


class Sitemap
{

    public static function RenderXML()
    {
        header("Content-Type: application/xml charset=utf-8");
        $database = Database::Connect();
        
        $xml = "<\x3Fxml version=\"1.0\" encoding=\"utf-8\"\x3F>\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $result = $database->Retrieve()
            ->UsingTable("categories")
            ->Item("title_mung-field")
            ->Match("publish_flag-checkbox", 1)
            ->Send();

        while($category = $result->Fetch())
        {
            $xml .= "\n\t<url>";
            $xml .= "\n\t\t<loc>".Utils::GenerateUri('categories',$category['title_mung-field']).'</loc>';
            $xml .= "\n\t</url>\n";
        }
        
        $result = $database->Retrieve()
            ->UsingTable("content")
            ->Match("meta-static_page_flag-checkbox", 1)
            ->AndWhere()->Match("publish-publish_flag-dropdown", 2)
            ->Send();
            
        while($pages = $result->Fetch())
        {
            $xml .= "\n\t<url>";
            $xml .= "\n\t\t<loc>".Utils::GenerateUri($pages['content-title_mung-field']).'</loc>';
            $xml .= "\n\t\t<lastmod>".Date::FromString($pages['meta-date-date'])->ToString("Y-m-d\x54H:i:s").Date::Offset().'</lastmod>';
            $xml .= "\n\t</url>\n";
        }
        
        $xml .= "\n</urlset>";
        
        echo $xml;
        exit;
    }
}

?>