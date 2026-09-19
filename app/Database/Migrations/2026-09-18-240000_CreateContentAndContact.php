<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentAndContact extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('business_settings', [
            'notification_email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true, 'after' => 'contact_email'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 80],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'eyebrow' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'summary' => ['type' => 'VARCHAR', 'constraint' => 320],
            'body' => ['type' => 'TEXT'],
            'meta_title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'meta_description' => ['type' => 'VARCHAR', 'constraint' => 320],
            'status' => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'draft'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('status');
        $this->forge->createTable('content_pages');

        $this->db->table('content_pages')->insertBatch([
            [
                'slug' => 'about',
                'title' => 'About the studio',
                'eyebrow' => 'The studio',
                'summary' => 'Introduce the designer, the point of view behind the work, and the clients the studio serves.',
                'body' => "Replace this starter copy with the story of your studio. Share how you work, what you value, and the kind of design partnerships you are looking for.",
                'meta_title' => 'About',
                'meta_description' => 'Learn about the independent design studio and its approach to thoughtful creative work.',
                'status' => 'published',
            ],
            [
                'slug' => 'contact',
                'title' => 'Start a conversation',
                'eyebrow' => 'Contact',
                'summary' => 'Tell us a little about what you are building and where design can help.',
                'body' => 'Share the essentials below. We will review your note and follow up using the email address you provide.',
                'meta_title' => 'Contact',
                'meta_description' => 'Contact the studio to discuss a new graphic design project.',
                'status' => 'published',
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy policy',
                'eyebrow' => 'Legal',
                'summary' => 'Explain how this website collects, uses, and retains visitor information.',
                'body' => 'Replace this draft with a privacy policy reviewed for your business and jurisdiction before publishing.',
                'meta_title' => 'Privacy policy',
                'meta_description' => 'Privacy information for this design studio website.',
                'status' => 'draft',
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms and conditions',
                'eyebrow' => 'Legal',
                'summary' => 'Set out the terms that govern use of this website.',
                'body' => 'Replace this draft with terms reviewed for your business and jurisdiction before publishing.',
                'meta_title' => 'Terms and conditions',
                'meta_description' => 'Terms for using this design studio website.',
                'status' => 'draft',
            ],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 160],
            'message' => ['type' => 'TEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'new'],
            'notification_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->createTable('contact_submissions');
    }

    public function down(): void
    {
        $this->forge->dropTable('contact_submissions');
        $this->forge->dropTable('content_pages');
        $this->forge->dropColumn('business_settings', 'notification_email');
    }
}
