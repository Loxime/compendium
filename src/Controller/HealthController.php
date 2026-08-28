<?php
namespace App\Controller;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
final class HealthController extends AbstractController
{
    #[Route('/health',name:'app_health',methods:['GET'])]
    public function __invoke(Connection $db,HttpClientInterface $http):JsonResponse
    {
        $database='ok';$search='ok';
        try{$db->executeQuery('SELECT 1')->fetchOne();}catch(\Throwable){$database='error';}
        try{$code=$http->request('GET',rtrim($_ENV['ELASTICSEARCH_URL']??'http://elasticsearch:9200','/'))->getStatusCode();if($code>=400)$search='error';}catch(\Throwable){$search='error';}
        $ok=$database==='ok'&&$search==='ok';return $this->json(['status'=>$ok?'ok':'degraded','database'=>$database,'elasticsearch'=>$search],$ok?200:503);
    }
}
