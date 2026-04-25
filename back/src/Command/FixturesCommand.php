<?php

namespace App\Command;

use App\Entity\Diplome;
use App\Entity\Etudiant;
use App\Entity\Filiere;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fixtures', description: 'Charge les données de test')]
class FixturesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('purge', null, InputOption::VALUE_NONE, 'Vider la base avant de charger');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('purge')) {
            $this->purge();
            $output->writeln('<comment>Base vidée.</comment>');
        }

        // ── Filières ──────────────────────────────────────────────
        $filieres = [];
        foreach (['Informatique', 'Gestion', 'Droit', 'Médecine'] as $nom) {
            $f = new Filiere();
            $f->setNom($nom);
            $this->em->persist($f);
            $filieres[$nom] = $f;
        }

        // ── Diplômes ──────────────────────────────────────────────
        $diplomes = [];
        $dipData = [
            'Licence Informatique'      => 'Informatique',
            'Master Informatique'       => 'Informatique',
            'Licence Gestion'           => 'Gestion',
            'Master Finance'            => 'Gestion',
            'Licence Droit Privé'       => 'Droit',
            'Master Droit des Affaires' => 'Droit',
            'PCEM1'                     => 'Médecine',
            'Doctorat Médecine'         => 'Médecine',
        ];
        foreach ($dipData as $intitule => $filiere) {
            $d = new Diplome();
            $d->setIntitule($intitule);
            $d->setFiliere($filieres[$filiere]);
            $this->em->persist($d);
            $diplomes[$intitule] = $d;
        }

        // ── Sessions ──────────────────────────────────────────────
        $sessions = [];
        $sesData = [
            ['Licence Informatique',      2025, 'Publié'],
            ['Master Informatique',       2025, 'Validé'],
            ['Licence Gestion',           2025, 'Publié'],
            ['Master Finance',            2025, 'Brouillon'],
            ['Licence Droit Privé',       2025, 'Publié'],
            ['PCEM1',                     2025, 'Validé'],
        ];
        foreach ($sesData as [$dip, $annee, $statut]) {
            $s = new Session();
            $s->setDiplome($diplomes[$dip]);
            $s->setAnnee($annee);
            $s->setStatut($statut);
            $this->em->persist($s);
            $sessions[] = $s;
        }

        $this->em->flush();

        // ── Étudiants ─────────────────────────────────────────────
        $prenoms = ['Alice', 'Baptiste', 'Camille', 'David', 'Emma', 'Fatima', 'Gabriel', 'Hana', 'Ibrahim', 'Julie'];
        $noms    = ['Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Petit', 'Richard', 'Simon', 'Laurent', 'Leroy'];
        $moyennes = [18.5, 15.0, 12.5, 9.0, 7.5, 14.0, 11.5, 8.0, 16.0, 13.0,
                     17.5, 6.0, 10.0, 19.0, 5.5, 12.0, 14.5, 9.5, 11.0, 16.5];

        $count = 0;
        foreach ($sessions as $idx => $session) {
            for ($i = 0; $i < 5; $i++) {
                $moyenne  = $moyennes[$count % count($moyennes)];
                $prenom   = $prenoms[$count % count($prenoms)];
                $nom      = $noms[($count + $i) % count($noms)];
                $admis    = $moyenne >= 10.0;
                $resultat = $admis ? 'Admis' : 'Ajourné';
                $annee    = $session->getAnnee();
                $numero   = sprintf('%d-ETU-%05d', $annee, $count + 1);

                $e = new Etudiant();
                $e->setPrenom($prenom);
                $e->setNom($nom);
                $e->setEmail(strtolower($prenom . '.' . $nom . $count . '@univ.fr'));
                $e->setMoyenne($moyenne);
                $e->setEstAdmis($admis);
                $e->setResultat($resultat);
                $e->setNumeroEtudiant($numero);
                $e->setSession($session);
                $this->em->persist($e);
                $count++;
            }
        }

        // ── Taux de réussite ──────────────────────────────────────
        foreach ($sessions as $session) {
            if (in_array($session->getStatut(), ['Validé', 'Publié'])) {
                $etudiants = $session->getEtudiants();
                $total     = count($etudiants);
                $admis     = count(array_filter(
                    $etudiants->toArray(),
                    fn($e) => $e->getResultat() === 'Admis'
                ));
                $session->setTauxReussite($total > 0 ? round(($admis / $total) * 100, 1) : 0);
            }
        }

        $this->em->flush();

        $output->writeln('<info>✓ Fixtures chargées !</info>');
        $output->writeln(sprintf(
            '<info>  → %d filières, %d diplômes, %d sessions, %d étudiants</info>',
            count($filieres), count($diplomes), count($sessions), $count
        ));

        return Command::SUCCESS;
    }

    private function purge(): void
    {
        $conn = $this->em->getConnection();
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $conn->executeStatement('TRUNCATE TABLE etudiant');
        $conn->executeStatement('TRUNCATE TABLE session');
        $conn->executeStatement('TRUNCATE TABLE diplome');
        $conn->executeStatement('TRUNCATE TABLE filiere');
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
