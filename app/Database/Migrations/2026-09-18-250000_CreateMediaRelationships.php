<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMediaRelationships extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('media_assets', [
            'updated_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'created_at'],
        ]);

        $this->forge->addField([
            'service_id' => ['type' => 'INT', 'unsigned' => true],
            'media_id' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('service_id');
        $this->forge->addKey('media_id');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('media_id', 'media_assets', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('service_media');

        $this->forge->addField([
            'project_id' => ['type' => 'INT', 'unsigned' => true],
            'media_id' => ['type' => 'INT', 'unsigned' => true],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey(['project_id', 'media_id']);
        $this->forge->addKey(['project_id', 'sort_order']);
        $this->forge->addKey('media_id');
        $this->forge->addForeignKey('project_id', 'portfolio_projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('media_id', 'media_assets', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('portfolio_project_media');

        $this->forge->addField([
            'project_id' => ['type' => 'INT', 'unsigned' => true],
            'service_id' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey(['project_id', 'service_id']);
        $this->forge->addKey('service_id');
        $this->forge->addForeignKey('project_id', 'portfolio_projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('portfolio_project_services');
    }

    public function down(): void
    {
        $this->forge->dropTable('portfolio_project_services');
        $this->forge->dropTable('portfolio_project_media');
        $this->forge->dropTable('service_media');
        $this->forge->dropColumn('media_assets', 'updated_at');
    }
}
