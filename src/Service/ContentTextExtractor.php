<?php
namespace App\Service;
use App\Entity\Publication;
use App\Enum\ContentFormat;
final class ContentTextExtractor
{
    public function extract(Publication $p): string
    {
        $raw=$p->getContenu();
        if($p->getContenuFormat()===ContentFormat::HTML_DRIVE){return trim(preg_replace('/\s+/u',' ',strip_tags($raw))??'');}
        if($p->getContenuFormat()===ContentFormat::EDITORJS_JSON){$d=json_decode($raw,true); if(!is_array($d))return $raw; $parts=[]; foreach(($d['blocks']??[]) as $b){$data=$b['data']??[]; foreach(['text','caption','code','items'] as $k){if(!isset($data[$k]))continue; $v=$data[$k]; $parts[]=is_array($v)?implode(' ',array_map(fn($x)=>is_scalar($x)?(string)$x:'',$v)):(string)$v;}} return trim(strip_tags(implode(' ',$parts)));}
        return trim($raw);
    }
}
