<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    /**
     * Le login est géré automatiquement par Symfony via json_login dans security.yaml
     * Cette route est juste déclarée pour le routeur.
     */
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Jamais exécuté — Symfony intercepte cette route via json_login
        throw new \LogicException('Cette route est interceptée par le firewall Symfony.');
    }

    /**
     * Retourne les infos de l'utilisateur actuellement connecté.
     */
    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['authenticated' => false], 401);
        }

        return $this->json([
            'authenticated' => true,
            'user' => [
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }

    /**
     * Déconnexion API — invalide la session.
     * La route /api/auth/logout est gérée par le firewall Symfony (logout: path).
     */
    #[Route('/logout', name: 'api_auth_logout', methods: ['POST'])]
    public function logout(): void
    {
        // Jamais exécuté — Symfony intercepte cette route via logout dans security.yaml
        throw new \LogicException('Cette route est interceptée par le firewall Symfony.');
    }
}
