<?php
namespace App\Controller;
use App\Repository\FeaturedPublicationRepository;
use App\Repository\PublicationRepository;
use App\Repository\ThemeRepository;
use App\Service\ElasticsearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class HomeController extends AbstractController
{
    #[Route('/',name:'app_home',methods:['GET'])]
    public function index(Request $request,PublicationRepository $publications,ThemeRepository $themes,FeaturedPublicationRepository $featured,ElasticsearchService $search):Response
    {
        $q=trim((string)$request->query->get('q',''));$searchFallback=false;
        if($q!==''){try{$items=$publications->publishedByIds($search->searchIds($q));}catch(\Throwable){$items=$publications->fallbackSearch($q);$searchFallback=true;}}else{$items=$publications->latestPublished();}
        return $this->render('home/index.html.twig',['publications'=>$items,'themes'=>$themes->findBy([],['ordre'=>'ASC']),'featured'=>$featured->findBy([],['position'=>'ASC']),'query'=>$q,'search_fallback'=>$searchFallback]);
    }
}
