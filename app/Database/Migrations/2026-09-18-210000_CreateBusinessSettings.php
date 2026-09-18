<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessSettings extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true],
            'business_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'tagline' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT'],
            'contact_email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('business_settings');

        $this->db->table('business_settings')->insert([
            'id' => 1,
            'business_name' => 'Your design studio',
            'tagline' => 'Design that moves your business forward.',
            'description' => 'Tell visitors what you create and who you help.',
            'contact_email' => '',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('business_settings');
    }
}
