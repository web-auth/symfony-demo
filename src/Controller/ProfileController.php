<?php

declare(strict_types=1);

/*
 * This file is part of the Webauthn Demo project.
 *
 * (c) Florent Morselli
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\PublicKeyCredentialSource;
use App\Entity\User;
use App\Form\Data\RegisterPublicKey;
use App\Form\Handler\RegisterPublicKeyHandler;
use App\Form\Handler\RegisterUserHandler;
use App\Repository\PublicKeyCredentialSourceRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Webauthn\Bundle\Service\PublicKeyCredentialCreationOptionsFactory;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\SecurityBundle\Security\WebauthnUtils;

final class ProfileController
{
    private const DEVICE_REGISTRATION_REQUEST = 'DEVICE_REGISTRATION_REQUEST';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var WebauthnUtils
     */
    private $webauthnUtils;

    /**
     * @var RegisterUserHandler
     */
    private $registerUserHandler;

    /**
     * @var RegisterPublicKeyHandler
     */
    private $registerPublicKeyHandler;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PublicKeyCredentialCreationOptionsFactory
     */
    private $publicKeyCredentialCreationOptionsFactory;

    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $credentialRepository;

    public function __construct(PublicKeyCredentialCreationOptionsFactory $publicKeyCredentialCreationOptionsFactory, RouterInterface $router, RegisterUserHandler $registerUserHandler, RegisterPublicKeyHandler $registerPublicKeyHandler, Environment $twig, TokenStorageInterface $tokenStorage, WebauthnUtils $webauthnUtils, PublicKeyCredentialSourceRepository $credentialRepository)
    {
        $this->twig = $twig;
        $this->tokenStorage = $tokenStorage;
        $this->webauthnUtils = $webauthnUtils;
        $this->registerUserHandler = $registerUserHandler;
        $this->registerPublicKeyHandler = $registerPublicKeyHandler;
        $this->router = $router;
        $this->publicKeyCredentialCreationOptionsFactory = $publicKeyCredentialCreationOptionsFactory;
        $this->credentialRepository = $credentialRepository;
    }

    public function profile(): Response
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $credentials = $this->credentialRepository->allForUser($user);
        $page = $this->twig->render('profile/home.html.twig', [
            'token' => $token,
            'user' => $user,
            'credentials' => $credentials,
        ]);

        return new Response($page);
    }

    public function creation(Request $request): Response
    {
        $token = $this->tokenStorage->getToken();
        /** @var User $user */
        $user = $token->getUser();

        $data = new RegisterPublicKey();
        $form = $this->registerPublicKeyHandler->prepare($data);

        $publicKeyCredentialCreationOptions = $this->getPublicKeyCredentialCreationOptions($request, $user);
        if ($this->registerPublicKeyHandler->process($request, $form, $publicKeyCredentialCreationOptions, $user)) {
            $request->getSession()->getFlashBag()->add(
                'success',
               'A new security device has been added'
            );
            $request->getSession()->remove(self::DEVICE_REGISTRATION_REQUEST);

            return new RedirectResponse(
                $this->router->generate('app_profile')
            );
        }
        $request->getSession()->remove(self::DEVICE_REGISTRATION_REQUEST);
        $publicKeyCredentialCreationOptions = $this->getPublicKeyCredentialCreationOptions($request, $user);

        $page = $this->twig->render('profile/device_registration.html.twig', [
            'form' => $form->createView(),
            'publicKeyCredentialCreationOptions' => $publicKeyCredentialCreationOptions,
        ]);

        return new Response($page);
    }

    private function getPublicKeyCredentialCreationOptions(Request $request, User $user): PublicKeyCredentialCreationOptions
    {
        $publicKeyCredentialCreationOptions = $request->getSession()->get(self::DEVICE_REGISTRATION_REQUEST);

        if (!$publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions) {
            $excludedCredentials = array_map(function (PublicKeyCredentialSource $credential) {
                return $credential->getPublicKeyCredentialDescriptor();
            }, $user->getPublicKeyCredentialSources());
            $publicKeyCredentialCreationOptions = $this->publicKeyCredentialCreationOptionsFactory->create(
                'default',
                new PublicKeyCredentialUserEntity($user->getUsername(), $user->getId(), $user->getDisplayName(), null),
                $excludedCredentials
            );
            $request->getSession()->set(self::DEVICE_REGISTRATION_REQUEST, $publicKeyCredentialCreationOptions);
        }

        return $publicKeyCredentialCreationOptions;
    }
}
