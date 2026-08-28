<?php
namespace App\Command;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
#[AsCommand(name:'app:user:promote-admin',description:'Promote an existing user to ROLE_ADMIN')]
final class PromoteAdminCommand extends Command
{
    public function __construct(private UserRepository $users,private EntityManagerInterface $em){parent::__construct();}
    protected function configure():void{$this->addArgument('email',InputArgument::REQUIRED);}
    protected function execute(InputInterface $input,OutputInterface $output):int{$io=new SymfonyStyle($input,$output);$email=mb_strtolower(trim((string)$input->getArgument('email')));$u=$this->users->findOneBy(['email'=>$email]);if(!$u){$io->error('Utilisateur introuvable.');return Command::FAILURE;}$u->setRoles([...$u->getRoles(),'ROLE_ADMIN']);$this->em->flush();$io->success($email.' est administrateur.');return Command::SUCCESS;}
}
