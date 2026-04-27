<?php

namespace App\Controller\Api;

use App\Entity\Etudiant;
use App\Repository\EtudiantRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/etudiants')]
class EtudiantController extends AbstractController
{
    private function serialize(Etudiant $e): array
    {
        return [
            'id'             => $e->getId(),
            'numeroEtudiant' => $e->getNumeroEtudiant(),
            'nom'            => $e->getNom(),
            'prenom'         => $e->getPrenom(),
            'email'          => $e->getEmail(),
            'moyenne'        => $e->getMoyenne(),
            'resultat'       => $e->getResultat() ?? 'En attente',
            'session'        => $e->getSession() ? [
                'id'     => $e->getSession()->getId(),
                'annee'  => $e->getSession()->getAnnee(),
                'statut' => $e->getSession()->getStatut(),
                'diplome' => $e->getSession()->getDiplome() ? [
                    'id'       => $e->getSession()->getDiplome()->getId(),
                    'intitule' => $e->getSession()->getDiplome()->getIntitule(),
                ] : null,
            ] : null,
        ];
    }

    /**
     * Génère un numéro étudiant unique.
     * Format : ANNEE-ABCD-00042
     */
    private function generateNumero(Etudiant $e, EntityManagerInterface $em): string
    {
        $annee   = $e->getSession()?->getAnnee() ?? (int) date('Y');
        $diplome = strtoupper(substr(
            preg_replace('/[^A-Za-z]/', '', $e->getSession()?->getDiplome()?->getIntitule() ?? 'ETU'),
            0, 4
        ));

        do {
            $numero = sprintf('%s-%s-%05d', $annee, $diplome, random_int(1, 99999));
            $exists = $em->getRepository(Etudiant::class)->findOneBy(['numeroEtudiant' => $numero]);
        } while ($exists !== null);

        return $numero;
    }

    #[Route('', name: 'api_etudiant_index', methods: ['GET'])]
    public function index(EtudiantRepository $repo, Request $request): JsonResponse
    {
        $sessionId = $request->query->get('session_id');
        $etudiants = $sessionId
            ? $repo->findBy(['session' => $sessionId], ['nom' => 'ASC'])
            : $repo->findBy([], ['nom' => 'ASC']);

        return $this->json(array_map($this->serialize(...), $etudiants));
    }

    #[Route('/{id}', name: 'api_etudiant_show', methods: ['GET'])]
    public function show(Etudiant $etudiant): JsonResponse
    {
        return $this->json($this->serialize($etudiant));
    }

    #[Route('', name: 'api_etudiant_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SessionRepository $sessionRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['session_id'])) {
            return $this->json(['error' => 'Nom, prénom, email et session sont obligatoires.'], 422);
        }

        $session = $sessionRepo->find($data['session_id']);
        if (!$session) {
            return $this->json(['error' => 'Session introuvable.'], 404);
        }

        $etudiant = new Etudiant();
        $etudiant->setNom(trim(strtoupper($data['nom'])));
        $etudiant->setPrenom(trim($data['prenom']));
        $etudiant->setEmail(trim($data['email']));
        $etudiant->setSession($session);
        $etudiant->setNumeroEtudiant($this->generateNumero($etudiant, $em));

        if (isset($data['moyenne']) && $data['moyenne'] !== '') {
            $etudiant->setMoyenne((float) $data['moyenne']);
        }
        if (!empty($data['resultat'])) {
            $etudiant->setResultat($data['resultat']);
        }

        $em->persist($etudiant);
        $em->flush();

        return $this->json($this->serialize($etudiant), 201);
    }

    #[Route('/{id}', name: 'api_etudiant_update', methods: ['PUT'])]
    public function update(
        Etudiant $etudiant,
        Request $request,
        EntityManagerInterface $em,
        SessionRepository $sessionRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nom']))    $etudiant->setNom(trim(strtoupper($data['nom'])));
        if (isset($data['prenom'])) $etudiant->setPrenom(trim($data['prenom']));
        if (isset($data['email']))  $etudiant->setEmail(trim($data['email']));

        if (!empty($data['session_id'])) {
            $s = $sessionRepo->find($data['session_id']);
            if ($s) $etudiant->setSession($s);
        }
        if (array_key_exists('moyenne', $data)) {
            $etudiant->setMoyenne(
                $data['moyenne'] !== null && $data['moyenne'] !== ''
                    ? (float) $data['moyenne']
                    : null
            );
        }
        if (array_key_exists('resultat', $data)) {
            $etudiant->setResultat(!empty($data['resultat']) ? $data['resultat'] : null);
        }

        $em->flush();
        return $this->json($this->serialize($etudiant));
    }

    #[Route('/{id}', name: 'api_etudiant_delete', methods: ['DELETE'])]
    public function delete(Etudiant $etudiant, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($etudiant);
        $em->flush();
        return $this->json(['success' => true]);
    }

    /**
     * Import CSV — format attendu (séparateur ; ou ,) :
     *   nom;prenom;email;date_naissance
     *   DUPONT;Jean;jean@test.com;2000-05-12
     *
     * POST multipart/form-data :
     *   - csv        : fichier .csv
     *   - session_id : ID de la session cible
     */
    #[Route('/import/csv', name: 'api_etudiant_import_csv', methods: ['POST'])]
    public function importCsv(
        Request $request,
        EntityManagerInterface $em,
        SessionRepository $sessionRepo
    ): JsonResponse {
        $sessionId = $request->request->get('session_id');
        if (!$sessionId) {
            return $this->json(['error' => 'Le paramètre session_id est obligatoire.'], 422);
        }

        $session = $sessionRepo->find($sessionId);
        if (!$session) {
            return $this->json(['error' => 'Session introuvable.'], 404);
        }

        $file = $request->files->get('csv');
        if (!$file) {
            return $this->json(['error' => 'Aucun fichier CSV fourni.'], 422);
        }

        $handle = fopen($file->getPathname(), 'r');
        if (!$handle) {
            return $this->json(['error' => 'Impossible de lire le fichier.'], 500);
        }

        $sep      = $this->detectSeparator($file->getPathname());
        $header   = fgetcsv($handle, 1000, $sep);
        $header   = array_map(fn($h) => strtolower(trim(str_replace([' ', '_'], '', $h))), $header);
        $imported = 0;
        $errors   = [];
        $line     = 0;

        while (($row = fgetcsv($handle, 1000, $sep)) !== false) {
            $line++;
            if (count($row) < 3) {
                $errors[] = "Ligne $line : données insuffisantes, ignorée.";
                continue;
            }

            $data   = array_combine($header, array_map('trim', $row));
            $nom    = strtoupper($data['nom']    ?? $row[0] ?? '');
            $prenom = $data['prenom']             ?? $row[1] ?? '';
            $email  = strtolower($data['email']   ?? $row[2] ?? '');

            if (empty($nom) || empty($prenom) || empty($email)) {
                $errors[] = "Ligne $line : champs manquants, ignorée.";
                continue;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Ligne $line : email '$email' invalide, ignorée.";
                continue;
            }

            $etudiant = new Etudiant();
            $etudiant->setNom($nom)->setPrenom($prenom)->setEmail($email)->setSession($session);
            $etudiant->setNumeroEtudiant($this->generateNumero($etudiant, $em));

            $dateStr = $data['datenaissance'] ?? $data['daten'] ?? $row[3] ?? '';
            if (!empty($dateStr)) {
                try {
                    $etudiant->setDateNaissance(new \DateTime($dateStr));
                } catch (\Exception) {}
            }

            $em->persist($etudiant);
            $imported++;
        }

        fclose($handle);
        $em->flush();

        return $this->json([
            'success'  => true,
            'imported' => $imported,
            'errors'   => $errors,
            'message'  => "$imported étudiant(s) importé(s).",
        ], 201);
    }

    private function detectSeparator(string $filePath): string
    {
        $f    = fopen($filePath, 'r');
        $line = fgets($f);
        fclose($f);
        return substr_count($line, ';') >= substr_count($line, ',') ? ';' : ',';
    }
}
