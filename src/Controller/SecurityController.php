<?php
namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
final class SecurityController extends AbstractController
{
    #[Route('/connexion',name:'app_login',methods:['GET','POST'])]
    public function login(Request $request,UserRepository $users,EntityManagerInterface $em,LoginLinkHandlerInterface $handler,MailerInterface $mailer):Response
    {
        if($this->getUser())return $this->redirectToRoute('app_home');
        if($request->isMethod('POST')){
            if(!$this->isCsrfTokenValid('login_request',(string)$request->request->get('_token')))throw $this->createAccessDeniedException();
            $email=mb_strtolower(trim((string)$request->request->get('email'))); if(!filter_var($email,FILTER_VALIDATE_EMAIL)){$this->addFlash('error','Adresse e-mail invalide.');return $this->redirectToRoute('app_login');}
            $user=$users->findOneBy(['email'=>$email]); if(!$user){$prenom=trim((string)$request->request->get('prenom'));$nom=trim((string)$request->request->get('nom'));if($prenom===''||$nom===''){$this->addFlash('error','Prénom et nom sont requis pour créer le compte.');return $this->redirectToRoute('app_login');}$user=(new User())->setEmail($email)->setPrenom($prenom)->setNom($nom)->setCodePostal(trim((string)$request->request->get('code_postal'))?:null);$em->persist($user);$em->flush();}
            $link=$handler->createLoginLink($user);$mailer->send((new TemplatedEmail())->to($user->getEmail())->subject('Votre lien de connexion Compendium')->htmlTemplate('emails/login_link.html.twig')->context(['user'=>$user,'loginUrl'=>$link->getUrl()]));
            return $this->render('security/link_sent.html.twig',['email'=>$email]);
        }
        return $this->render('security/login.html.twig');
    }
    #[Route('/connexion/check',name:'app_login_check')] public function check():never{throw new \LogicException('Intercepted by Symfony Security.');}
    #[Route('/deconnexion',name:'app_logout')] public function logout():never{throw new \LogicException('Intercepted by Symfony Security.');}
}
