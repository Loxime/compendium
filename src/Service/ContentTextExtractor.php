<?php
namespace App\Service;
use App\Entity\Publication;
use App\Enum\ContentFormat;
final class ContentTextExtractor
{
    public function extract(Publication $p): string
    {
        $raw=$p->getContenu();
        if($p->getContenuFormat()===ContentFormat::HTML_DRIVE){$raw=preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is',' ',$raw)??$raw;return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($raw),ENT_QUOTES|ENT_HTML5,'UTF-8'))??'');}
        if($p->getContenuFormat()===ContentFormat::EDITORJS_JSON){$d=json_decode($raw,true); if(!is_array($d))return $raw; $parts=[]; foreach(($d['blocks']??[]) as $b){$data=$b['data']??[]; foreach(['text','caption','code','items'] as $k){if(!isset($data[$k]))continue; $v=$data[$k]; $parts[]=is_array($v)?implode(' ',array_map(fn($x)=>is_scalar($x)?(string)$x:'',$v)):(string)$v;}} return trim(strip_tags(implode(' ',$parts)));}
        return trim($raw);
    }
}
