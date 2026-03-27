<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration initiale pour MySQL (XAMPP / phpMyAdmin)
 * Crée toutes les tables du projet depuis zéro.
 *
 * À utiliser à la place des anciennes migrations SQLite.
 * Commande : php bin/console doctrine:migrations:migrate --no-interaction
 */
final class Version20260323000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création complète du schéma MySQL pour la plateforme de résultats d\'examens';
    }

    public function up(Schema $schema): void
    {
        // Table filiere
        $this->addSql('
            CREATE TABLE filiere (
                id         INT AUTO_INCREMENT NOT NULL,
                nom        VARCHAR(255)       NOT NULL,
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Table diplome
        $this->addSql('
            CREATE TABLE diplome (
                id          INT AUTO_INCREMENT NOT NULL,
                filiere_id  INT                NOT NULL,
                intitule    VARCHAR(255)       NOT NULL,
                PRIMARY KEY (id),
                INDEX IDX_diplome_filiere (filiere_id),
                CONSTRAINT FK_diplome_filiere
                    FOREIGN KEY (filiere_id)
                    REFERENCES filiere (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Table session
        $this->addSql('
            CREATE TABLE session (
                id             INT AUTO_INCREMENT NOT NULL,
                diplome_id     INT                NOT NULL,
                annee          INT                NOT NULL,
                statut         VARCHAR(255)       NOT NULL DEFAULT "Brouillon",
                taux_reussite  DOUBLE PRECISION   DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX IDX_session_diplome (diplome_id),
                CONSTRAINT FK_session_diplome
                    FOREIGN KEY (diplome_id)
                    REFERENCES diplome (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Table etudiant
        $this->addSql('
            CREATE TABLE etudiant (
                id              INT AUTO_INCREMENT NOT NULL,
                session_id      INT                NOT NULL,
                nom             VARCHAR(255)       NOT NULL,
                prenom          VARCHAR(255)       NOT NULL,
                email           VARCHAR(255)       NOT NULL,
                moyenne         DOUBLE PRECISION   DEFAULT NULL,
                est_admis       TINYINT(1)         DEFAULT NULL,
                resultat        VARCHAR(20)        DEFAULT NULL,
                date_naissance  DATE               DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX IDX_etudiant_session (session_id),
                CONSTRAINT FK_etudiant_session
                    FOREIGN KEY (session_id)
                    REFERENCES session (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Table user
        $this->addSql('
            CREATE TABLE user (
                id       INT AUTO_INCREMENT NOT NULL,
                email    VARCHAR(180)       NOT NULL,
                roles    JSON               NOT NULL,
                password VARCHAR(255)       NOT NULL,
                PRIMARY KEY (id),
                UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Table messenger_messages (Symfony Messenger)
        $this->addSql('
            CREATE TABLE messenger_messages (
                id           BIGINT AUTO_INCREMENT NOT NULL,
                body         LONGTEXT              NOT NULL,
                headers      LONGTEXT              NOT NULL,
                queue_name   VARCHAR(190)          NOT NULL,
                created_at   DATETIME              NOT NULL COMMENT "(DC2Type:datetime_immutable)",
                available_at DATETIME              NOT NULL COMMENT "(DC2Type:datetime_immutable)",
                delivered_at DATETIME              DEFAULT NULL COMMENT "(DC2Type:datetime_immutable)",
                PRIMARY KEY (id),
                INDEX IDX_75EA56E0FB7336F0 (queue_name),
                INDEX IDX_75EA56E0E3BD61CE (available_at),
                INDEX IDX_75EA56E016BA31DB (delivered_at)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS etudiant');
        $this->addSql('DROP TABLE IF EXISTS session');
        $this->addSql('DROP TABLE IF EXISTS diplome');
        $this->addSql('DROP TABLE IF EXISTS filiere');
        $this->addSql('DROP TABLE IF EXISTS user');
        $this->addSql('DROP TABLE IF EXISTS messenger_messages');
    }
}
