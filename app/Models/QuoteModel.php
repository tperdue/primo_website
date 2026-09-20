<?php

namespace App\Models;

use CodeIgniter\Model;

class QuoteModel extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'ready' => 'Ready to send',
        'sent' => 'Sent',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'expired' => 'Expired',
        'void' => 'Void',
    ];

    public const PUBLIC_STATUSES = ['ready', 'sent', 'accepted', 'declined', 'expired'];

    protected $table = 'quotes';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'quote_number', 'access_token', 'customer_id', 'quote_request_id', 'customer_name',
        'customer_business_name', 'customer_email', 'customer_phone', 'quote_date', 'expires_on',
        'currency_code', 'status', 'subtotal', 'discount_amount', 'tax_rate', 'tax_amount',
        'total', 'deposit_percentage', 'deposit_amount', 'terms', 'notes', 'version',
        'delivery_status', 'sent_at', 'responded_at', 'customer_response_note',
    ];
}
