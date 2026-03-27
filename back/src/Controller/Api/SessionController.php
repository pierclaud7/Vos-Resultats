<?php

namespace App\Controller\Api;

use App\Entity\Session;
use App\Repository\DiplomeRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sessions')]
class SessionController extends AbstractController
{
    private function serializeSession(Session $s): array
    {
        return [
            'id'           => $s->getId(),
            'annee'        => $s->getAnnee(),
            'statut'       => $s->getStatut(),
            'tauxReussite' => $s->getTauxReussite(),
            'diplome'      => $s->getDiplome() ? [
                'id'       => $s->getDiplome()->getId(),
                'intitule' => $s->getDiplome()->getIntitule(),
                'filiere'  => $s->getDiplome()->getFiliere() ? [
                    'id'  => $s->getDiplome()->getFiliere()->getId(),
                    'nom' => $s->getDiplome()->getFiliere()->getNom(),
                ] : null,
            ] : null,
            'nbEtudiants'  => $s->getEtudiants()->count(),
        ];
    }

    #[Route('', name: 'api_session_index', methods: ['GET'])]
    public function index(SessionRepository $repo): JsonResponse
    {
        $sessions = $repo->findAll();
        return $this->json(array_map([$this, 'serializeSession'], $sessions));
    }

    #[Route('/{id}', name: 'api_session_show', methods: ['GET'])]
    public function show(Session $session): JsonResponse
    {
        return $this->json($this->serializeSession($session));
    }

    #[Route('', name: 'api_session_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        DiplomeRepository $diplomeRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['annee']) || empty($data['diplome_id'])) {
            return $this->json(['error' => "L'année et le diplôme sont obligatoires."], 422);
        }

        $diplome = $diplomeRepo->find($data['diplome_id']);
        if (!$diplome) {
            return $this->json(['error' => 'Diplôme introuvable.'], 404);
        }

        $session = new Session();
        $session->setAnnee((int) $data['annee']);
        $session->setDiplome($diplome);
        $session->setStatut('Brouillon');

        $em->persist($session);
        $em->flush();

        return $this->json($this->serializeSession($session), 201);
    }

    #[Route('/{id}', name: 'api_session_update', methods: ['PUT'])]
    public function update(
        Session $session,
        Request $request,
        EntityManagerInterface $em,
        DiplomeRepository $diplomeRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['annee'])) {
            $session->setAnnee((int) $data['annee']);
        }
        if (isset($data['diplome_id'])) {
            $diplome = $diplomeRepo->find($data['diplome_id']);
            if ($diplome) {
                $session->setDiplome($diplome);
            }
        }

        $em->flush();

        return $this->json($this->serializeSession($session));
    }

    /**
     * Étape 1 : Valider une session
     * → Calcule automatiquement le taux de réussite
     * → Passe le statut à "Validé"
     */
    #[Route('/{id}/valider', name: 'api_session_valider', methods: ['POST'])]
    public function valider(Session $session, EntityManagerInterface $em): JsonResponse
    {
        if ($session->getStatut() === 'Publié') {
            return $this->json(['error' => 'Session déjà publiée, impossible de revalider.'], 409);
        }

        $etudiants = $session->getEtudiants();
        $total = $etudiants->count();

        if ($total === 0) {
            return $this->json(['error' => 'Aucun étudiant dans cette session.'], 422);
        }

        // Calcul automatique du taux de réussite
        $nbAdmis = 0;
        foreach ($etudiants as $etudiant) {
            if ($etudiant->isEstAdmis()) {
                $nbAdmis++;
            }
        }

        $taux = round(($nbAdmis / $total) * 100, 2);

        $session->setTauxReussite($taux);
        $session->setStatut('Validé');
        $em->flush();

        return $this->json([
            'success'      => true,
            'message'      => 'Session validée avec succès.',
            'tauxReussite' => $taux,
            'nbAdmis'      => $nbAdmis,
            'total'        => $total,
            'session'      => $this->serializeSession($session),
        ]);
    }

    /**
     * Étape 2 : Publier une session
     * → La rend visible sur le site public
     * → Passe le statut à "Publié"
     */
    #[Route('/{id}/publier', name: 'api_session_publier', methods: ['POST'])]
    public function publier(Session $session, EntityManagerInterface $em): JsonResponse
    {
        if ($session->getStatut() !== 'Validé') {
            return $this->json([
                'error' => 'La session doit d\'abord être validée avant d\'être publiée.',
            ], 409);
        }

        $session->setStatut('Publié');
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Session publiée avec succès. Les résultats sont maintenant visibles publiquement.',
            'session' => $this->serializeSession($session),
        ]);
    }

    #[Route('/{id}', name: 'api_session_delete', methods: ['DELETE'])]
    public function delete(Session $session, EntityManagerInterface $em): JsonResponse
    {
        if ($session->getStatut() === 'Publié') {
            return $this->json(['error' => 'Impossible de supprimer une session publiée.'], 409);
        }

        $em->remove($session);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
