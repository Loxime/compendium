<?php
namespace App\Service;
use App\Entity\Publication;
use App\Enum\PublicationStatus;
use Symfony\Contracts\HttpClient\HttpClientInterface;
final class ElasticsearchService
{
    private const INDEX='compendium_publications';
    public function __construct(private HttpClientInterface $httpClient, private ContentTextExtractor $extractor, private string $baseUrl) { $this->baseUrl=rtrim($baseUrl,'/'); }
    public function ensureIndex(): void
    {
        try { $r=$this->httpClient->request('HEAD',$this->baseUrl.'/'.self::INDEX); if($r->getStatusCode()===200)return; } catch(\Throwable) {}
        $this->httpClient->request('PUT',$this->baseUrl.'/'.self::INDEX,['json'=>['mappings'=>['properties'=>['titre'=>['type'=>'text','analyzer'=>'standard'],'contenu'=>['type'=>'text','analyzer'=>'standard'],'theme'=>['type'=>'keyword'],'langue'=>['type'=>'keyword'],'type'=>['type'=>'keyword']]]]])->getContent(false);
    }
    public function index(Publication $p): void
    {
        if(!$p->getId())return; if($p->getStatut()!==PublicationStatus::PUBLIE){$this->delete($p);return;} $this->ensureIndex();
        $this->httpClient->request('PUT',$this->baseUrl.'/'.self::INDEX.'/_doc/'.$p->getId(),['json'=>['titre'=>$p->getTitre(),'contenu'=>$this->extractor->extract($p),'theme'=>$p->getTheme()?->getSlug(),'langue'=>$p->getLangue(),'type'=>$p->getType()->value]])->getContent(false);
    }
    public function delete(Publication $p): void { if(!$p->getId())return; try{$this->httpClient->request('DELETE',$this->baseUrl.'/'.self::INDEX.'/_doc/'.$p->getId())->getContent(false);}catch(\Throwable){} }
    public function searchIds(string $query,int $limit=20): array
    {
        $this->ensureIndex(); $r=$this->httpClient->request('POST',$this->baseUrl.'/'.self::INDEX.'/_search',['json'=>['size'=>$limit,'query'=>['multi_match'=>['query'=>$query,'fields'=>['titre^3','contenu'],'fuzziness'=>'AUTO']]]]); $d=$r->toArray(false); return array_values(array_map('intval',array_column($d['hits']['hits']??[],'_id')));
    }
}
