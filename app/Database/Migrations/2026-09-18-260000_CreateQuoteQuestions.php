<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuoteQuestions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('name');
        $this->forge->addKey(['is_active', 'sort_order']);
        $this->forge->createTable('quote_question_groups');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'group_id' => ['type' => 'INT', 'unsigned' => true],
            'label' => ['type' => 'VARCHAR', 'constraint' => 180],
            'field_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'help_text' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'placeholder' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['group_id', 'is_active', 'sort_order']);
        $this->forge->addForeignKey('group_id', 'quote_question_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quote_questions');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'question_id' => ['type' => 'INT', 'unsigned' => true],
            'label' => ['type' => 'VARCHAR', 'constraint' => 180],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['question_id', 'sort_order']);
        $this->forge->addForeignKey('question_id', 'quote_questions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quote_question_options');

        $this->forge->addField([
            'group_id' => ['type' => 'INT', 'unsigned' => true],
            'service_id' => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey(['group_id', 'service_id']);
        $this->forge->addKey('service_id');
        $this->forge->addForeignKey('group_id', 'quote_question_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quote_question_group_services');
    }

    public function down(): void
    {
        $this->forge->dropTable('quote_question_group_services');
        $this->forge->dropTable('quote_question_options');
        $this->forge->dropTable('quote_questions');
        $this->forge->dropTable('quote_question_groups');
    }
}
