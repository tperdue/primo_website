<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePortfolioAndMedia extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'alt_text' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'byte_size' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('media_assets');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 160],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'client_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'summary' => ['type' => 'VARCHAR', 'constraint' => 320],
            'challenge' => ['type' => 'TEXT'],
            'solution' => ['type' => 'TEXT'],
            'project_date' => ['type' => 'DATE', 'null' => true],
            'featured_media_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'draft'],
            'is_featured' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey(['status', 'sort_order']);
        $this->forge->addForeignKey('featured_media_id', 'media_assets', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('portfolio_projects');
    }

    public function down(): void
    {
        $this->forge->dropTable('portfolio_projects');
        $this->forge->dropTable('media_assets');
    }
}
