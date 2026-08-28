<?php
namespace App\Controller;
use App\Entity\Theme;
use App\Enum\PublicationStatus;
use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class ThemeController extends AbstractController
{
    #[Route('/theme/{slug}',name:'app_theme_show',methods:['GET'])]
    public function show(Theme $theme,PublicationRepository $repo):Response{return $this->render('theme/show.html.twig',['theme'=>$theme,'publications'=>$repo->findBy(['theme'=>$theme,'statut'=>PublicationStatus::PUBLIE],['createdAt'=>'DESC'])]);}
}
