<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappCampaignLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'customer_id',
        'phone',
        'whatsapp_instance_id',
        'status',
        'error'
    ];

    public function campaign()
    {
        return $this->belongsTo(WhatsappCampaign::class);
    }
}



