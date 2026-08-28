<?php
declare(strict_types=1);
namespace App\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Webauthn\Bundle\Security\Authentication\WebauthnAuthenticator;
use Webauthn\Bundle\Security\Authentication\WebauthnBadge;
use Webauthn\Bundle\Security\Authentication\WebauthnPassport;
final class PasskeyAuthenticator extends WebauthnAuthenticator
{
    public function __construct(private UrlGeneratorInterface $urls){}
    public function authenticate(Request $r):Passport{return new WebauthnPassport(new WebauthnBadge($r->getHost(),(string)$r->request->get('_assertion','')));}
    public function onAuthenticationSuccess(Request $r,TokenInterface $t,string $f):?Response{return new Response('',302,['Location'=>$this->urls->generate('app_home')]);}
    protected function getLoginUrl(Request $r):string{return $this->urls->generate('app_login');}
}
