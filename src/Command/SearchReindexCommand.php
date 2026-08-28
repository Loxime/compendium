<?php
namespace App\Command;
use App\Enum\PublicationStatus;
use App\Repository\PublicationRepository;
use App\Service\ElasticsearchService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(name:'app:search:reindex',description:'Reindex published publications in Elasticsearch')]
final class SearchReindexCommand extends Command
{
    public function __construct(private PublicationRepository $repo,private ElasticsearchService $search){parent::__construct();}
    protected function execute(InputInterface $input,OutputInterface $output):int{$this->search->ensureIndex();foreach($this->repo->findBy(['statut'=>PublicationStatus::PUBLIE]) as $p)$this->search->index($p);$output->writeln('<info>Indexation terminée.</info>');return Command::SUCCESS;}
}
