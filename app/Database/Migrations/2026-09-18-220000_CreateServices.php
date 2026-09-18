<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServices extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 160],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'summary' => ['type' => 'VARCHAR', 'constraint' => 300],
            'description' => ['type' => 'TEXT'],
            'starting_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'currency_code' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'USD'],
            'show_price' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'status' => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'draft'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['status', 'sort_order']);
        $this->forge->createTable('services');
    }

    public function down(): void
    {
        $this->forge->dropTable('services');
    }
}
