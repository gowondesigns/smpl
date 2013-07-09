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

    public static function CreateXML($languageCode = null)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        $xml .= '</urlset>';
        
        echo $xml;
    }
}

/*
<?xml version="1.0" encoding="UTF-8"?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

   <url>

      <loc>http://www.example.com/</loc>

      <lastmod>2005-01-01</lastmod>

      <changefreq>monthly</changefreq>

      <priority>0.8</priority>

   </url>

</urlset>
//*/
?>