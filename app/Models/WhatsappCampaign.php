<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class WhatsappCampaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'target_audience',
        'filter_newsletter',
        'filter_customer_type',
        'test_customer_id',
        'scheduled_at',
        'scheduled_time',
        'interval_seconds',
        'total_leads',
        'processed_count',
        'status'
    ];

    protected $casts = [
        'filter_newsletter' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    public function testCustomer()
    {
        return $this->belongsTo(Customer::class, 'test_customer_id');
    }

    public function logs()
    {
        return $this->hasMany(WhatsappCampaignLog::class, 'campaign_id');
    }
}








