<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQuoteResponsesAndProjects extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('quotes', [
            'responded_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'sent_at'],
            'customer_response_note' => ['type' => 'TEXT', 'null' => true, 'after' => 'responded_at'],
        ]);
        $this->forge->addColumn('quote_status_history', [
            'actor' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'admin', 'after' => 'note'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'project_number' => ['type' => 'VARCHAR', 'constraint' => 30],
            'quote_id' => ['type' => 'INT', 'unsigned' => true],
            'customer_id' => ['type' => 'INT', 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'start_date' => ['type' => 'DATE', 'null' => true],
            'due_date' => ['type' => 'DATE', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'awaiting_deposit'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'customer_update' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('project_number');
        $this->forge->addUniqueKey('quote_id');
        $this->forge->addKey(['status', 'updated_at']);
        $this->forge->addForeignKey('quote_id', 'quotes', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('projects');
    }

    public function down(): void
    {
        $this->forge->dropTable('projects');
        $this->forge->dropColumn('quote_status_history', 'actor');
        $this->forge->dropColumn('quotes', ['responded_at', 'customer_response_note']);
    }
}
