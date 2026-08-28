<?php
declare(strict_types=1);
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
final class SecurityController extends AbstractController
{
    #[Route('/connexion',name:'app_login',methods:['GET','POST'])]
    public function login(AuthenticationUtils $auth):Response{if($this->getUser())return $this->redirectToRoute('app_home');return $this->render('security/login.html.twig',['error'=>$auth->getLastAuthenticationError(),'last_username'=>$auth->getLastUsername()]);}
    #[Route('/inscription',name:'app_register',methods:['GET'])] public function register():Response{return $this->render('security/register.html.twig');}
    #[Route('/deconnexion',name:'app_logout')] public function logout():never{throw new \LogicException('Intercepté par Symfony Security.');}
}
