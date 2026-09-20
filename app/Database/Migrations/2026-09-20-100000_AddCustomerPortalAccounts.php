<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerPortalAccounts extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('customers', [
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
        ]);
        $customers = $this->db->protectIdentifiers($this->db->prefixTable('customers'));
        $this->db->query('CREATE UNIQUE INDEX customers_user_id_unique ON ' . $customers . ' (user_id)');

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'INT', 'unsigned' => true],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'expires_at' => ['type' => 'DATETIME'],
            'used_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('customer_id');
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addKey(['expires_at', 'used_at']);
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('customer_portal_invitations');
    }

    public function down(): void
    {
        $this->forge->dropTable('customer_portal_invitations');
        $this->forge->dropKey('customers', 'customers_user_id_unique', false);
        $this->forge->dropColumn('customers', 'user_id');
    }
}
