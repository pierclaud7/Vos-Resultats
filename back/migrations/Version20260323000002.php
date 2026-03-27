<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout du champ numero_etudiant (identifiant unique pour consultation publique).
 * Uniquement utile si tu as déjà la base sans ce champ.
 * Si tu repars de zéro, la migration V1 suffit.
 */
final class Version20260323000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout numéro étudiant unique pour la consultation publique';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE etudiant ADD COLUMN numero_etudiant VARCHAR(50) DEFAULT NULL');
        $this->addSql("UPDATE etudiant SET numero_etudiant = CONCAT('ETU-', LPAD(id, 5, '0')) WHERE numero_etudiant IS NULL");
        $this->addSql('ALTER TABLE etudiant MODIFY numero_etudiant VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_etudiant_numero ON etudiant (numero_etudiant)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_etudiant_numero ON etudiant');
        $this->addSql('ALTER TABLE etudiant DROP COLUMN numero_etudiant');
    }
}
