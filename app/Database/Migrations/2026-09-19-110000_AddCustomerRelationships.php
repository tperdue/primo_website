<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerRelationships extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('customers', [
            'normalized_email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true, 'after' => 'email'],
            'address_line_1' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true, 'after' => 'phone'],
            'address_line_2' => ['type' => 'VARCHAR', 'constraint' => 180, 'null' => true, 'after' => 'address_line_1'],
            'city' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'address_line_2'],
            'region' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'city'],
            'postal_code' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'region'],
            'country' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'after' => 'postal_code'],
        ]);
        $this->forge->addColumn('quote_requests', [
            'customer_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
            'normalized_email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true, 'after' => 'email'],
        ]);

        $customersByEmail = [];
        foreach ($this->db->table('customers')->select('id, email')->orderBy('id', 'ASC')->get()->getResultArray() as $customer) {
            $normalized = strtolower(trim((string) $customer['email']));
            $this->db->table('customers')->where('id', $customer['id'])->update(['normalized_email' => $normalized]);
            $customersByEmail[$normalized] ??= (int) $customer['id'];
        }

        foreach ($this->db->table('quote_requests')->select('id, email')->get()->getResultArray() as $request) {
            $normalized = strtolower(trim((string) $request['email']));
            $update = ['normalized_email' => $normalized];
            if (isset($customersByEmail[$normalized])) {
                $update['customer_id'] = $customersByEmail[$normalized];
            }
            $this->db->table('quote_requests')->where('id', $request['id'])->update($update);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('quote_requests', ['customer_id', 'normalized_email']);
        $this->forge->dropColumn('customers', [
            'normalized_email', 'address_line_1', 'address_line_2', 'city', 'region', 'postal_code', 'country',
        ]);
    }
}
