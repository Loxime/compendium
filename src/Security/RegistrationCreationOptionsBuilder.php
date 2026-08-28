<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Webauthn\Bundle\CredentialOptionsBuilder\ProfileBasedCreationOptionsBuilder;
use Webauthn\Bundle\CredentialOptionsBuilder\PublicKeyCredentialCreationOptionsBuilder;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialUserEntity;

final readonly class RegistrationCreationOptionsBuilder implements PublicKeyCredentialCreationOptionsBuilder
{
    public function __construct(private ProfileBasedCreationOptionsBuilder $profileBuilder)
    {
    }

    public function getFromRequest(
        Request $request,
        PublicKeyCredentialUserEntity $userEntity,
        bool $hideExistingExcludedCredentials = false,
    ): PublicKeyCredentialCreationOptions {
        if ($request->getContentTypeFormat() !== 'form') {
            return $this->profileBuilder->getFromRequest($request, $userEntity, $hideExistingExcludedCredentials);
        }

        $optionsRequest = Request::create(
            $request->getUri(),
            $request->getMethod(),
            server: ['CONTENT_TYPE' => 'application/json'],
            content: '{}',
        );

        return $this->profileBuilder->getFromRequest(
            $optionsRequest,
            $userEntity,
            $hideExistingExcludedCredentials,
        );
    }
}
