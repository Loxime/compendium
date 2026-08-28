<?php
namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Publication;
use App\Entity\Reaction;
use App\Entity\User;
use App\Enum\PublicationStatus;
use App\Enum\ReactionType;
use App\Repository\ReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicationController extends AbstractController
{
    #[Route('/publication/{id}',name:'app_publication_show',requirements:['id'=>'\d+'],methods:['GET'])]
    public function show(Publication $publication):Response
    {
        if($publication->getStatut()!==PublicationStatus::PUBLIE && !$this->isGranted('ROLE_ADMIN')) throw $this->createNotFoundException();
        return $this->render('publication/show.html.twig',['publication'=>$publication]);
    }

    #[Route('/publication/{id}/avis',name:'app_publication_comment',requirements:['id'=>'\d+'],methods:['POST'])]
    public function comment(Publication $publication,Request $request,EntityManagerInterface $em):Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if(!$this->isCsrfTokenValid('comment_'.$publication->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();
        $content=trim((string)$request->request->get('contenu'));
        if($content!=='' && mb_strlen($content)<=4000){$user=$this->getUser();if($user instanceof User){$avis=(new Avis())->setPublication($publication)->setUser($user)->setContenu($content);$em->persist($avis);$em->flush();}}
        return $this->redirectToRoute('app_publication_show',['id'=>$publication->getId()]);
    }

    #[Route('/publication/{id}/reaction/{type}',name:'app_publication_react',requirements:['id'=>'\d+','type'=>'like|dislike'],methods:['POST'])]
    public function react(Publication $publication,string $type,Request $request,ReactionRepository $reactions,EntityManagerInterface $em):Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if(!$this->isCsrfTokenValid('react_'.$publication->getId(),(string)$request->request->get('_token')))throw $this->createAccessDeniedException();
        $user=$this->getUser(); if(!$user instanceof User)throw $this->createAccessDeniedException(); $wanted=ReactionType::from($type); $existing=$reactions->findOneBy(['publication'=>$publication,'user'=>$user]);
        if(!$existing){$reaction=(new Reaction())->setPublication($publication)->setUser($user)->setType($wanted);$em->persist($reaction);$wanted===ReactionType::LIKE?$publication->setNbLikes($publication->getNbLikes()+1):$publication->setNbDislikes($publication->getNbDislikes()+1);}
        elseif($existing->getType()===$wanted){$wanted===ReactionType::LIKE?$publication->setNbLikes($publication->getNbLikes()-1):$publication->setNbDislikes($publication->getNbDislikes()-1);$em->remove($existing);}
        else{$existing->getType()===ReactionType::LIKE?$publication->setNbLikes($publication->getNbLikes()-1):$publication->setNbDislikes($publication->getNbDislikes()-1);$wanted===ReactionType::LIKE?$publication->setNbLikes($publication->getNbLikes()+1):$publication->setNbDislikes($publication->getNbDislikes()+1);$existing->setType($wanted);}
        $em->flush(); return $this->redirectToRoute('app_publication_show',['id'=>$publication->getId()]);
    }
}
