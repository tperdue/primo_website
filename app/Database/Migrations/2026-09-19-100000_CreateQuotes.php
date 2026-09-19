<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQuotes extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('business_settings', [
            'default_quote_expiration_days' => ['type' => 'INT', 'default' => 30, 'after' => 'notification_email'],
            'default_quote_terms' => ['type' => 'TEXT', 'null' => true, 'after' => 'default_quote_expiration_days'],
            'default_deposit_percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '50.00', 'after' => 'default_quote_terms'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'business_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->createTable('customers');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_number' => ['type' => 'VARCHAR', 'constraint' => 30],
            'access_token' => ['type' => 'CHAR', 'constraint' => 64],
            'customer_id' => ['type' => 'INT', 'unsigned' => true],
            'quote_request_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'customer_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'customer_business_name' => ['type' => 'VARCHAR', 'constraint' => 160, 'null' => true],
            'customer_email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'customer_phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'quote_date' => ['type' => 'DATE'],
            'expires_on' => ['type' => 'DATE'],
            'currency_code' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'USD'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'subtotal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'discount_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'tax_rate' => ['type' => 'DECIMAL', 'constraint' => '6,3', 'default' => '0.000'],
            'tax_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'total' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'deposit_percentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => '0.00'],
            'deposit_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00'],
            'terms' => ['type' => 'TEXT', 'null' => true],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'version' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'delivery_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('quote_number');
        $this->forge->addUniqueKey('access_token');
        $this->forge->addUniqueKey('quote_request_id');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('quote_request_id', 'quote_requests', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('quotes');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_id' => ['type' => 'INT', 'unsigned' => true],
            'description' => ['type' => 'VARCHAR', 'constraint' => 200],
            'details' => ['type' => 'TEXT', 'null' => true],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'line_total' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['quote_id', 'sort_order']);
        $this->forge->addForeignKey('quote_id', 'quotes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quote_line_items');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_id' => ['type' => 'INT', 'unsigned' => true],
            'from_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'to_status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'note' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['quote_id', 'created_at']);
        $this->forge->addForeignKey('quote_id', 'quotes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quote_status_history');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'quote_id' => ['type' => 'INT', 'unsigned' => true],
            'version_number' => ['type' => 'INT', 'unsigned' => true],
            'snapshot_json' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['quote_id', 'version_number']);
        $this->forge->addForeignKey('quote_id', 'quotes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('quote_versions');
    }

    public function down(): void
    {
        $this->forge->dropTable('quote_versions');
        $this->forge->dropTable('quote_status_history');
        $this->forge->dropTable('quote_line_items');
        $this->forge->dropTable('quotes');
        $this->forge->dropTable('customers');
        $this->forge->dropColumn('business_settings', ['default_quote_expiration_days', 'default_quote_terms', 'default_deposit_percentage']);
    }
}
