<?php
namespace App\Controller;

use App\Entity\FeaturedPublication;
use App\Entity\Publication;
use App\Entity\Theme;
use App\Enum\ContentFormat;
use App\Enum\PublicationSource;
use App\Enum\PublicationStatus;
use App\Enum\PublicationType;
use App\Repository\FeaturedPublicationRepository;
use App\Repository\PublicationRepository;
use App\Repository\ThemeRepository;
use App\Service\DriveSyncStatus;
use App\Service\ElasticsearchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('',name:'app_admin',methods:['GET'])]
    public function dashboard(PublicationRepository $pubs,ThemeRepository $themes,DriveSyncStatus $drive):Response{return $this->render('admin/dashboard.html.twig',['publication_count'=>$pubs->count([]),'theme_count'=>$themes->count([]),'drive_enabled'=>$drive->enabled(),'drive_missing'=>$drive->missingConfiguration()]);}

    #[Route('/themes',name:'app_admin_themes',methods:['GET','POST'])]
    public function themes(Request $request,ThemeRepository $repo,EntityManagerInterface $em):Response
    {
        if($request->isMethod('POST')){if(!$this->isCsrfTokenValid('theme_new',(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$name=trim((string)$request->request->get('nom'));if($name!==''){$slug=$this->slug((string)$request->request->get('slug',$name));$t=(new Theme())->setNom($name)->setSlug($slug)->setIconeFontawesome(trim((string)$request->request->get('icone','fa-solid fa-book')))->setOrdre((int)$request->request->get('ordre',0));$em->persist($t);$em->flush();return $this->redirectToRoute('app_admin_themes');}}
        return $this->render('admin/themes.html.twig',['themes'=>$repo->findBy([],['ordre'=>'ASC'])]);
    }

    #[Route('/themes/{id}/edit',name:'app_admin_theme_edit',methods:['GET','POST'])]
    public function themeEdit(Theme $theme,Request $request,EntityManagerInterface $em):Response
    {
        if($request->isMethod('POST')){if(!$this->isCsrfTokenValid('theme_edit_'.$theme->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$theme->setNom(trim((string)$request->request->get('nom')))->setSlug($this->slug((string)$request->request->get('slug')))->setIconeFontawesome(trim((string)$request->request->get('icone')))->setOrdre((int)$request->request->get('ordre'));$em->flush();return $this->redirectToRoute('app_admin_themes');}
        return $this->render('admin/theme_edit.html.twig',['theme'=>$theme]);
    }

    #[Route('/themes/{id}/delete',name:'app_admin_theme_delete',methods:['POST'])]
    public function themeDelete(Theme $theme,Request $request,EntityManagerInterface $em):Response{if(!$this->isCsrfTokenValid('theme_delete_'.$theme->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();try{$em->remove($theme);$em->flush();}catch(\Throwable){$this->addFlash('error','Impossible de supprimer un thème utilisé par une publication.');}return $this->redirectToRoute('app_admin_themes');}

    #[Route('/publications',name:'app_admin_publications',methods:['GET'])]
    public function publications(PublicationRepository $repo):Response{return $this->render('admin/publications.html.twig',['publications'=>$repo->findBy([],['updatedAt'=>'DESC'])]);}

    #[Route('/publications/new',name:'app_admin_publication_new',methods:['GET','POST'])]
    public function publicationNew(Request $request,ThemeRepository $themes,EntityManagerInterface $em,ElasticsearchService $search):Response
    {
        $p=new Publication(); if($request->isMethod('POST')){$this->hydratePublication($p,$request,$themes);if(!$this->isCsrfTokenValid('publication_new',(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$em->persist($p);$em->flush();try{$search->index($p);}catch(\Throwable){}return $this->redirectToRoute('app_admin_publications');}
        return $this->render('admin/publication_form.html.twig',['publication'=>$p,'themes'=>$themes->findBy([],['ordre'=>'ASC']),'csrf_id'=>'publication_new','title'=>'Nouvelle publication']);
    }

    #[Route('/publications/{id}/edit',name:'app_admin_publication_edit',methods:['GET','POST'])]
    public function publicationEdit(Publication $publication,Request $request,ThemeRepository $themes,EntityManagerInterface $em,ElasticsearchService $search):Response
    {
        if($request->isMethod('POST')){if(!$this->isCsrfTokenValid('publication_edit_'.$publication->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$this->hydratePublication($publication,$request,$themes);$em->flush();try{$search->index($publication);}catch(\Throwable){}return $this->redirectToRoute('app_admin_publications');}
        return $this->render('admin/publication_form.html.twig',['publication'=>$publication,'themes'=>$themes->findBy([],['ordre'=>'ASC']),'csrf_id'=>'publication_edit_'.$publication->getId(),'title'=>'Modifier la publication']);
    }

    #[Route('/publications/{id}/delete',name:'app_admin_publication_delete',methods:['POST'])]
    public function publicationDelete(Publication $publication,Request $request,EntityManagerInterface $em,ElasticsearchService $search):Response{if(!$this->isCsrfTokenValid('publication_delete_'.$publication->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();try{$search->delete($publication);}catch(\Throwable){}$em->remove($publication);$em->flush();return $this->redirectToRoute('app_admin_publications');}

    #[Route('/featured',name:'app_admin_featured',methods:['GET','POST'])]
    public function featured(Request $request,PublicationRepository $pubs,FeaturedPublicationRepository $featured,EntityManagerInterface $em):Response
    {
        if($request->isMethod('POST')){if(!$this->isCsrfTokenValid('featured_save',(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$position=max(1,min(10,(int)$request->request->get('position',1)));$publication=$pubs->find((int)$request->request->get('publication_id'));if($publication){foreach($featured->findBy(['position'=>$position]) as $old)$em->remove($old);foreach($featured->findBy(['publication'=>$publication]) as $old)$em->remove($old);$em->flush();$item=(new FeaturedPublication())->setPublication($publication)->setPosition($position);$em->persist($item);$em->flush();}return $this->redirectToRoute('app_admin_featured');}
        return $this->render('admin/featured.html.twig',['featured'=>$featured->findBy([],['position'=>'ASC']),'publications'=>$pubs->findBy(['statut'=>PublicationStatus::PUBLIE],['titre'=>'ASC'])]);
    }

    #[Route('/featured/{id}/delete',name:'app_admin_featured_delete',methods:['POST'])]
    public function featuredDelete(FeaturedPublication $item,Request $request,EntityManagerInterface $em):Response{if(!$this->isCsrfTokenValid('featured_delete_'.$item->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$em->remove($item);$em->flush();return $this->redirectToRoute('app_admin_featured');}

    #[Route('/drive',name:'app_admin_drive',methods:['GET','POST'])]
    public function drive(Request $request,DriveSyncStatus $status,ThemeRepository $themes,PublicationRepository $pubs,EntityManagerInterface $em,ElasticsearchService $search):Response
    {
        if($request->isMethod('POST')){if(!$this->isCsrfTokenValid('drive_demo',(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$fileId=trim((string)$request->request->get('drive_file_id','demo-google-doc-1'));$p=$pubs->findOneBy(['driveFileId'=>$fileId])??(new Publication())->setDriveFileId($fileId)->setSource(PublicationSource::DRIVE);$theme=$themes->find((int)$request->request->get('theme_id'))??$themes->findOneBy([]);$p->setTheme($theme)->setTitre(trim((string)$request->request->get('titre','[FR] Document Drive de démonstration')))->setLangue((string)$request->request->get('langue','fr'))->setContenu((string)$request->request->get('contenu','Contenu HTML importé depuis le simulateur Drive.'))->setContenuFormat(ContentFormat::HTML_DRIVE)->setStatut(PublicationStatus::BROUILLON);if(!$p->getId())$em->persist($p);$em->flush();try{$search->delete($p);}catch(\Throwable){}$this->addFlash('success','Import simulé : la publication est en brouillon, comme après une modification Drive.');return $this->redirectToRoute('app_admin_drive');}
        return $this->render('admin/drive.html.twig',['drive_enabled'=>$status->enabled(),'missing'=>$status->missingConfiguration(),'themes'=>$themes->findBy([],['ordre'=>'ASC'])]);
    }

    private function hydratePublication(Publication $p,Request $r,ThemeRepository $themes):void{$theme=$themes->find((int)$r->request->get('theme_id'));if(!$theme)throw $this->createNotFoundException('Thème introuvable.');$p->setTheme($theme)->setTitre(trim((string)$r->request->get('titre')))->setContenu((string)$r->request->get('contenu'))->setLangue(trim((string)$r->request->get('langue','fr')))->setType(PublicationType::from((string)$r->request->get('type','note')))->setContenuFormat(ContentFormat::from((string)$r->request->get('contenu_format','texte_brut')))->setStatut(PublicationStatus::from((string)$r->request->get('statut','brouillon')));}
    private function slug(string $s):string{$s=trim(mb_strtolower($s));$s=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s)?:$s;$s=preg_replace('/[^a-z0-9]+/','-',$s)??'';return trim($s,'-')?:'theme';}
}
