<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api')]
class ProfileController extends AbstractController
{

    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
      $this->tokenStorage = $tokenStorage;
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(): Response
    {
      $token = $this->tokenStorage->getToken();

      if (!$token) {
        throw new AccessDeniedHttpException();
      }

      $user = $token->getUser();

      return $this->json(['email' => $user->getEmail()], Response::HTTP_OK);
    }
}
