<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SimplifyQuoteRequestStatuses extends Migration
{
    public function up(): void
    {
        $table = $this->db->table('quote_requests');
        $table->whereIn('status', ['reviewing', 'needs_information'])->update(['status' => 'needs_information']);
        $table->whereIn('status', ['qualified', 'quote_preparing'])->update(['status' => 'ready_to_quote']);
        $table->whereIn('status', ['quote_sent', 'accepted', 'declined'])->update(['status' => 'closed_quote_created']);
        $table->whereIn('status', ['not_qualified', 'closed'])->update(['status' => 'closed_wont_pursue']);

        foreach ($this->db->table('quotes')->select('quote_request_id')->where('quote_request_id IS NOT NULL')->get()->getResultArray() as $quote) {
            $table->where('id', $quote['quote_request_id'])->update(['status' => 'closed_quote_created']);
        }
    }

    public function down(): void
    {
        $table = $this->db->table('quote_requests');
        $table->where('status', 'needs_information')->update(['status' => 'new']);
        $table->where('status', 'ready_to_quote')->update(['status' => 'qualified']);
        $table->where('status', 'closed_quote_created')->update(['status' => 'quote_preparing']);
        $table->where('status', 'closed_wont_pursue')->update(['status' => 'closed']);

    }
}
