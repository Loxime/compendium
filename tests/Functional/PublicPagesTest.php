<?php
declare(strict_types=1);
namespace App\Tests\Functional;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
final class PublicPagesTest extends WebTestCase
{
    public function testHomepageHasEditorialSearchAndNavigation():void{$client=static::createClient();$client->request('GET','/');self::assertResponseIsSuccessful();self::assertSelectorTextContains('h1','Des travaux');self::assertSelectorExists('#recherche');self::assertSelectorExists('.mega');}
    public function testRegistrationAndLoginOnlyOfferPasskeys():void{$client=static::createClient();$client->request('GET','/inscription');self::assertResponseIsSuccessful();self::assertSelectorTextContains('#passkey-register button[type="submit"]','PASSKEY');self::assertSelectorNotExists('input[type=password]');$client->request('GET','/connexion');self::assertResponseIsSuccessful();self::assertSelectorTextContains('#passkey-login button[type="submit"]','PASSKEY');self::assertSelectorNotExists('input[type=password]');}
    public function testAdminRequiresAuthentication():void{$client=static::createClient();$client->request('GET','/admin');self::assertTrue(in_array($client->getResponse()->getStatusCode(),[302,401,403],true));}
}
