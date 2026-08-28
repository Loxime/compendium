<?php
declare(strict_types=1);
namespace App\Twig;
use App\Repository\ThemeRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
final class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private ThemeRepository $themes){}
    public function getGlobals():array{return ['global_themes'=>$this->themes->findBy([],['ordre'=>'ASC'])];}
}
