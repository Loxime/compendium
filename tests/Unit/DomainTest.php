<?php
declare(strict_types=1);
namespace App\Tests\Unit;
use App\Entity\FeaturedPublication;
use App\Entity\Publication;
use App\Entity\Theme;
use App\Entity\User;
use App\Enum\ContentFormat;
use App\Service\ContentTextExtractor;
use PHPUnit\Framework\TestCase;
final class DomainTest extends TestCase
{
    public function testUserNeverLosesRoleUser():void{$user=(new User())->setPrenom('Ada')->setNom('Lovelace')->setEmail('ADA@example.test')->setRoles([]);self::assertSame('ada@example.test',$user->getEmail());self::assertContains('ROLE_USER',$user->getRoles());}
    public function testFeaturedPositionIsLimitedToTen():void{$featured=new FeaturedPublication();$featured->setPosition(10);self::assertSame(10,$featured->getPosition());$this->expectException(\InvalidArgumentException::class);$featured->setPosition(11);}
    public function testSearchExtractionNeverIndexesRawEditorJsonOrHtml():void{$theme=(new Theme())->setNom('Test')->setSlug('test');$html=(new Publication())->setTheme($theme)->setContenuFormat(ContentFormat::HTML_DRIVE)->setContenu('<h2>Titre</h2><script>danger()</script>');$json=(new Publication())->setTheme($theme)->setContenuFormat(ContentFormat::EDITORJS_JSON)->setContenu('{"blocks":[{"type":"paragraph","data":{"text":"Texte <b>utile</b>"}}]}');$extractor=new ContentTextExtractor();self::assertStringNotContainsString('<h2>',$extractor->extract($html));self::assertSame('Texte utile',$extractor->extract($json));}
}
