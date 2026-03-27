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
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures',
    description: 'Charge des données de test dans la base de données',
)]
class FixturesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('purge', null, InputOption::VALUE_NONE, 'Vider la base avant de charger les fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Chargement des données de test');

        if ($input->getOption('purge')) {
            $this->purge();
            $io->warning('Base de données vidée.');
        }

        // ── Filières ──────────────────────────────────────────────────────────
        $filieres = [];
        foreach (['Informatique', 'Gestion', 'Pharmacie', 'Commerce'] as $nom) {
            $f = new Filiere();
            $f->setNom($nom);
            $this->em->persist($f);
            $filieres[$nom] = $f;
        }
        $this->em->flush();
        $io->text('✓ 4 filières créées');

        // ── Diplômes ──────────────────────────────────────────────────────────
        $diplomesData = [
            'Informatique' => ['BTS SIO', 'Licence Informatique', 'Master IA'],
            'Gestion'      => ['BTS Compta', 'Licence Gestion', 'Master Finance'],
            'Pharmacie'    => ['Licence Pharmacie', 'Master Pharmacologie'],
            'Commerce'     => ['BTS MCO', 'Licence Commerce International'],
        ];

        $diplomes = [];
        foreach ($diplomesData as $filiereName => $intitules) {
            foreach ($intitules as $intitule) {
                $d = new Diplome();
                $d->setIntitule($intitule);
                $d->setFiliere($filieres[$filiereName]);
                $this->em->persist($d);
                $diplomes[$intitule] = $d;
            }
        }
        $this->em->flush();
        $io->text('✓ ' . count($diplomes) . ' diplômes créés');

        // ── Sessions ──────────────────────────────────────────────────────────
        $sessions = [];
        $sessionsData = [
            ['BTS SIO',              2024, 'Publié',    78.5],
            ['BTS SIO',              2025, 'Validé',    82.0],
            ['Licence Informatique', 2024, 'Publié',    65.0],
            ['Master IA',            2024, 'Publié',    90.0],
            ['BTS Compta',           2024, 'Publié',    71.0],
            ['Licence Gestion',      2025, 'Brouillon', null],
            ['BTS MCO',              2024, 'Publié',    68.5],
        ];

        foreach ($sessionsData as [$diplomeNom, $annee, $statut, $taux]) {
            $s = new Session();
            $s->setDiplome($diplomes[$diplomeNom]);
            $s->setAnnee($annee);
            $s->setStatut($statut);
            $s->setTauxReussite($taux);
            $this->em->persist($s);
            $sessions[] = $s;
        }
        $this->em->flush();
        $io->text('✓ ' . count($sessions) . ' sessions créées');

        // ── Étudiants ─────────────────────────────────────────────────────────
        $etudiantsData = [
            ['DUPONT',   'Jean',     'jean.dupont@test.com',   14.5, true,  $sessions[0]],
            ['MARTIN',   'Sophie',   'sophie.martin@test.com', 12.0, true,  $sessions[0]],
            ['BERNARD',  'Lucas',    'lucas.bernard@test.com',  8.5, false, $sessions[0]],
            ['DUBOIS',   'Emma',     'emma.dubois@test.com',    6.0, false, $sessions[0]],
            ['THOMAS',   'Léo',      'leo.thomas@test.com',    15.5, true,  $sessions[0]],
            ['ROBERT',   'Claire',   'claire.robert@test.com', 11.0, true,  $sessions[1]],
            ['RICHARD',  'Antoine',  'antoine.richard@test.com',9.0, false, $sessions[1]],
            ['LEROY',    'Julie',    'julie.leroy@test.com',   16.0, true,  $sessions[2]],
            ['MOREAU',   'Paul',     'paul.moreau@test.com',    7.5, false, $sessions[2]],
            ['SIMON',    'Alice',    'alice.simon@test.com',   13.5, true,  $sessions[3]],
            ['LAURENT',  'Hugo',     'hugo.laurent@test.com',  18.0, true,  $sessions[3]],
            ['LEFEBVRE', 'Camille',  'camille.lefebvre@test.com',10.0, true, $sessions[4]],
            ['MICHEL',   'Nathan',   'nathan.michel@test.com',  5.0, false, $sessions[4]],
            ['GARCIA',   'Inès',     'ines.garcia@test.com',   14.0, true,  $sessions[6]],
            ['DAVID',    'Maxime',   'maxime.david@test.com',   9.5, false, $sessions[6]],
        ];

        foreach ($etudiantsData as [$nom, $prenom, $email, $moyenne, $admis, $session]) {
            $e = new Etudiant();
            $e->setNom($nom)->setPrenom($prenom)->setEmail($email);
            $e->setMoyenne($moyenne)->setEstAdmis($admis)->setSession($session);

            // Numéro auto-généré
            $annee   = $session->getAnnee();
            $diplome = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $session->getDiplome()->getIntitule()), 0, 4));
            do {
                $numero = sprintf('%s-%s-%05d', $annee, $diplome, random_int(1, 99999));
                $exists = $this->em->getRepository(Etudiant::class)->findOneBy(['numeroEtudiant' => $numero]);
            } while ($exists !== null);
            $e->setNumeroEtudiant($numero);

            $this->em->persist($e);
        }
        $this->em->flush();
        $io->text('✓ ' . count($etudiantsData) . ' étudiants créés');

        $io->success('Données de test chargées avec succès !');
        $io->note('Connectez-vous avec : admin@test.com / admin123 (créer avec app:create-admin)');

        return Command::SUCCESS;
    }

    private function purge(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\Etudiant')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Session')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Diplome')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Filiere')->execute();
    }
}
