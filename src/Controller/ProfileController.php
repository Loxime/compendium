<?php
namespace App\Controller;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class ProfileController extends AbstractController
{
    #[Route('/profil',name:'app_profile',methods:['GET'])] public function index():Response{return $this->render('profile/index.html.twig');}
    #[Route('/profil/supprimer',name:'app_profile_delete',methods:['POST'])]
    public function delete(Request $request,EntityManagerInterface $em):Response{$this->denyAccessUnlessGranted('ROLE_USER');if(!$this->isCsrfTokenValid('delete_account',(string)$request->request->get('_token')))throw $this->createAccessDeniedException();$user=$this->getUser();if($user instanceof User){$em->remove($user);$em->flush();$request->getSession()->invalidate();}return $this->redirectToRoute('app_home');}
}
