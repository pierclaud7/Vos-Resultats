<?php

namespace App\Controller\Api;

use App\Entity\Filiere;
use App\Repository\FiliereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/filieres')]
class FiliereController extends AbstractController
{
    #[Route('', name: 'api_filiere_index', methods: ['GET'])]
    public function index(FiliereRepository $repo): JsonResponse
    {
        $filieres = $repo->findAll();

        $data = array_map(fn(Filiere $f) => [
            'id'           => $f->getId(),
            'nom'          => $f->getNom(),
            'nbDiplomes'   => $f->getDiplomes()->count(),
        ], $filieres);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'api_filiere_show', methods: ['GET'])]
    public function show(Filiere $filiere): JsonResponse
    {
        return $this->json([
            'id'  => $filiere->getId(),
            'nom' => $filiere->getNom(),
            'diplomes' => $filiere->getDiplomes()->map(fn($d) => [
                'id'       => $d->getId(),
                'intitule' => $d->getIntitule(),
            ])->toArray(),
        ]);
    }

    #[Route('', name: 'api_filiere_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom'])) {
            return $this->json(['error' => 'Le nom est obligatoire.'], 422);
        }

        $filiere = new Filiere();
        $filiere->setNom(trim($data['nom']));

        $em->persist($filiere);
        $em->flush();

        return $this->json([
            'id'  => $filiere->getId(),
            'nom' => $filiere->getNom(),
        ], 201);
    }

    #[Route('/{id}', name: 'api_filiere_update', methods: ['PUT'])]
    public function update(Filiere $filiere, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom'])) {
            return $this->json(['error' => 'Le nom est obligatoire.'], 422);
        }

        $filiere->setNom(trim($data['nom']));
        $em->flush();

        return $this->json([
            'id'  => $filiere->getId(),
            'nom' => $filiere->getNom(),
        ]);
    }

    #[Route('/{id}', name: 'api_filiere_delete', methods: ['DELETE'])]
    public function delete(Filiere $filiere, EntityManagerInterface $em): JsonResponse
    {
        if ($filiere->getDiplomes()->count() > 0) {
            return $this->json([
                'error' => 'Impossible de supprimer : cette filière contient des diplômes.',
            ], 409);
        }

        $em->remove($filiere);
        $em->flush();

        return $this->json(['success' => true]);
    }
}
