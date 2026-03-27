<?php

namespace App\Controller\Api;

use App\Entity\Diplome;
use App\Repository\DiplomeRepository;
use App\Repository\FiliereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/diplomes')]
class DiplomeController extends AbstractController
{
    #[Route('', name: 'api_diplome_index', methods: ['GET'])]
    public function index(DiplomeRepository $repo): JsonResponse
    {
        $diplomes = $repo->findAll();

        $data = array_map(fn(Diplome $d) => [
            'id'        => $d->getId(),
            'intitule'  => $d->getIntitule(),
            'filiere'   => $d->getFiliere() ? [
                'id'  => $d->getFiliere()->getId(),
                'nom' => $d->getFiliere()->getNom(),
            ] : null,
            'nbSessions' => $d->getSessions()->count(),
        ], $diplomes);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_diplome_show', methods: ['GET'])]
    public function show(Diplome $diplome): JsonResponse
    {
        return $this->json([
            'id'       => $diplome->getId(),
            'intitule' => $diplome->getIntitule(),
            'filiere'  => $diplome->getFiliere() ? [
                'id'  => $diplome->getFiliere()->getId(),
                'nom' => $diplome->getFiliere()->getNom(),
            ] : null,
            'sessions' => $diplome->getSessions()->map(fn($s) => [
                'id'    => $s->getId(),
                'annee' => $s->getAnnee(),
                'statut' => $s->getStatut(),
            ])->toArray(),
        ]);
    }

    #[Route('', name: 'api_diplome_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        FiliereRepository $filiereRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['intitule'])) {
            return $this->json(['error' => "L'intitulé est obligatoire."], 422);
        }
        if (empty($data['filiere_id'])) {
            return $this->json(['error' => 'La filière est obligatoire.'], 422);
        }

        $filiere = $filiereRepo->find($data['filiere_id']);
        if (!$filiere) {
            return $this->json(['error' => 'Filière introuvable.'], 404);
        }

        $diplome = new Diplome();
        $diplome->setIntitule(trim($data['intitule']));
        $diplome->setFiliere($filiere);

        $em->persist($diplome);
        $em->flush();

        return $this->json([
            'id'       => $diplome->getId(),
            'intitule' => $diplome->getIntitule(),
            'filiere'  => ['id' => $filiere->getId(), 'nom' => $filiere->getNom()],
        ], 201);
    }

    #[Route('/{id}', name: 'api_diplome_update', methods: ['PUT'])]
    public function update(
        Diplome $diplome,
        Request $request,
        EntityManagerInterface $em,
        FiliereRepository $filiereRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!empty($data['intitule'])) {
            $diplome->setIntitule(trim($data['intitule']));
        }
        if (!empty($data['filiere_id'])) {
            $filiere = $filiereRepo->find($data['filiere_id']);
            if ($filiere) {
                $diplome->setFiliere($filiere);
            }
        }

        $em->flush();

        return $this->json([
            'id'       => $diplome->getId(),
            'intitule' => $diplome->getIntitule(),
            'filiere'  => $diplome->getFiliere() ? [
                'id'  => $diplome->getFiliere()->getId(),
                'nom' => $diplome->getFiliere()->getNom(),
            ] : null,
        ]);
    }

    #[Route('/{id}', name: 'api_diplome_delete', methods: ['DELETE'])]
    public function delete(Diplome $diplome, EntityManagerInterface $em): JsonResponse
    {
        if ($diplome->getSessions()->count() > 0) {
            return $this->json([
                'error' => 'Impossible de supprimer : ce diplôme contient des sessions.',
            ], 409);
        }

        $em->remove($diplome);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
