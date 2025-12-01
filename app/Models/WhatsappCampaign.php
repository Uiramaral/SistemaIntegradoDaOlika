<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappCampaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'target_audience',
        'interval_seconds',
        'total_leads',
        'processed_count',
        'status'
    ];

    public function logs()
    {
        return $this->hasMany(WhatsappCampaignLog::class, 'campaign_id');
    }
}


