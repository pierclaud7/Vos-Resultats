<?php

namespace App\Controller\Api;

use App\Repository\FiliereRepository;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public')]
class PublicController extends AbstractController
{
    #[Route('/filieres', name: 'api_public_filieres', methods: ['GET'])]
    public function filieres(FiliereRepository $filiereRepo): JsonResponse
    {
        $data = [];
        foreach ($filiereRepo->findAll() as $filiere) {
            $hasPublished = false;
            foreach ($filiere->getDiplomes() as $d) {
                foreach ($d->getSessions() as $s) {
                    if ($s->getStatut() === 'Publié') { $hasPublished = true; break 2; }
                }
            }
            if ($hasPublished) {
                $data[] = ['id' => $filiere->getId(), 'nom' => $filiere->getNom()];
            }
        }
        return $this->json($data);
    }

    #[Route('/filieres/{id}/diplomes', name: 'api_public_diplomes', methods: ['GET'])]
    public function diplomesParFiliere(int $id, FiliereRepository $filiereRepo): JsonResponse
    {
        $filiere = $filiereRepo->find($id);
        if (!$filiere) { return $this->json(['error' => 'Filière introuvable.'], 404); }

        $data = [];
        foreach ($filiere->getDiplomes() as $diplome) {
            $sessions = [];
            foreach ($diplome->getSessions() as $session) {
                if ($session->getStatut() === 'Publié') {
                    $sessions[] = ['id' => $session->getId(), 'annee' => $session->getAnnee(), 'tauxReussite' => $session->getTauxReussite()];
                }
            }
            if (!empty($sessions)) {
                $data[] = ['id' => $diplome->getId(), 'intitule' => $diplome->getIntitule(), 'sessions' => $sessions];
            }
        }
        return $this->json($data);
    }

    /**
     * Recherche par : ?numeroEtudiant=... OU ?email=... OU ?nom=...&prenom=...
     */
    #[Route('/sessions/{id}/resultat', name: 'api_public_resultat', methods: ['GET'])]
    public function resultat(int $id, Request $request, SessionRepository $sessionRepo): JsonResponse
    {
        $session = $sessionRepo->find($id);
        if (!$session || $session->getStatut() !== 'Publié') {
            return $this->json(['error' => 'Session introuvable ou non publiée.'], 404);
        }

        $numero = trim($request->query->get('numeroEtudiant', ''));
        $email  = strtolower(trim($request->query->get('email', '')));
        $nom    = strtoupper(trim($request->query->get('nom', '')));
        $prenom = trim($request->query->get('prenom', ''));

        if (empty($numero) && empty($email) && (empty($nom) || empty($prenom))) {
            return $this->json(['error' => 'Fournissez votre numéro étudiant, email, ou nom et prénom.'], 422);
        }

        $etudiant = null;
        foreach ($session->getEtudiants() as $e) {
            if (
                (!empty($numero) && $e->getNumeroEtudiant() === $numero)
                || (!empty($email) && strtolower($e->getEmail()) === $email)
                || (!empty($nom) && !empty($prenom) && strtoupper($e->getNom()) === $nom && strtolower($e->getPrenom()) === strtolower($prenom))
            ) { $etudiant = $e; break; }
        }

        if (!$etudiant) {
            return $this->json(['error' => 'Aucun résultat trouvé avec ces informations.'], 404);
        }

        $resultat = 'En attente';
        if ($etudiant->isEstAdmis()) {
            $resultat = 'Admis';
        } elseif ($etudiant->getMoyenne() !== null) {
            $resultat = match(true) {
                $etudiant->getMoyenne() >= 10 => 'Admis',
                $etudiant->getMoyenne() >= 8  => 'Rattrapage',
                default                       => 'Refusé',
            };
        }

        return $this->json([
            'nom'            => $etudiant->getNom(),
            'prenom'         => $etudiant->getPrenom(),
            'numeroEtudiant' => $etudiant->getNumeroEtudiant(),
            'resultat'       => $resultat,
            'moyenne'        => $etudiant->getMoyenne(),
            'session'        => [
                'annee'   => $session->getAnnee(),
                'diplome' => $session->getDiplome()?->getIntitule(),
                'filiere' => $session->getDiplome()?->getFiliere()?->getNom(),
            ],
        ]);
    }

    #[Route('/statistiques', name: 'api_public_statistiques', methods: ['GET'])]
    public function statistiques(FiliereRepository $filiereRepo, Request $request): JsonResponse
    {
        $annee = $request->query->get('annee');
        $data  = [];
        foreach ($filiereRepo->findAll() as $filiere) {
            $diplomeStats = [];
            foreach ($filiere->getDiplomes() as $diplome) {
                $sessions = [];
                foreach ($diplome->getSessions() as $session) {
                    if ($session->getStatut() !== 'Publié') continue;
                    if ($annee && (string) $session->getAnnee() !== (string) $annee) continue;
                    $sessions[] = ['id' => $session->getId(), 'annee' => $session->getAnnee(), 'tauxReussite' => $session->getTauxReussite()];
                }
                if (!empty($sessions)) {
                    $diplomeStats[] = ['id' => $diplome->getId(), 'intitule' => $diplome->getIntitule(), 'sessions' => $sessions];
                }
            }
            if (!empty($diplomeStats)) {
                $data[] = ['id' => $filiere->getId(), 'nom' => $filiere->getNom(), 'diplomes' => $diplomeStats];
            }
        }
        return $this->json($data);
    }
}
