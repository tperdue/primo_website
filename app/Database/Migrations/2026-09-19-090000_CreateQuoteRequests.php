<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuoteRequests extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reference_number' => ['type' => 'VARCHAR', 'constraint' => 24],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'company' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'preferred_contact' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'email'],
            'project_summary' => ['type' => 'TEXT'],
            'desired_completion_date' => ['type' => 'DATE', 'null' => true],
            'budget_range' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'additional_notes' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'new'],
            'internal_notes' => ['type' => 'TEXT', 'null' => true],
            'owner_notification_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'customer_notification_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('reference_number');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addKey('email');
        $this->forge->createTable('quote_requests');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_request_id' => ['type' => 'INT', 'unsigned' => true],
            'service_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'service_name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'service_slug' => ['type' => 'VARCHAR', 'constraint' => 160],
            'service_summary' => ['type' => 'VARCHAR', 'constraint' => 500],
            'service_description' => ['type' => 'TEXT'],
            'starting_price' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'currency_code' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'USD'],
            'show_price' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['quote_request_id', 'sort_order']);
        $this->forge->addForeignKey('quote_request_id', 'quote_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('quote_request_services');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_request_id' => ['type' => 'INT', 'unsigned' => true],
            'question_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'group_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'question_label' => ['type' => 'VARCHAR', 'constraint' => 180],
            'field_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'answer_text' => ['type' => 'TEXT', 'null' => true],
            'answer_json' => ['type' => 'TEXT', 'null' => true],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['quote_request_id', 'sort_order']);
        $this->forge->addForeignKey('quote_request_id', 'quote_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('question_id', 'quote_questions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('quote_request_answers');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_request_id' => ['type' => 'INT', 'unsigned' => true],
            'question_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'relative_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 120],
            'extension' => ['type' => 'VARCHAR', 'constraint' => 20],
            'size_bytes' => ['type' => 'BIGINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('quote_request_id');
        $this->forge->addForeignKey('quote_request_id', 'quote_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('question_id', 'quote_questions', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('quote_request_files');
    }

    public function down(): void
    {
        $this->forge->dropTable('quote_request_files');
        $this->forge->dropTable('quote_request_answers');
        $this->forge->dropTable('quote_request_services');
        $this->forge->dropTable('quote_requests');
    }
}
