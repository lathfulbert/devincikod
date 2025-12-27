<?php

namespace Modules\WhatsAppMarketing\Models;

use App\Core\Database\Model;

class WhatsAppMessage extends Model
{
    protected static string $table = 'whatsapp_messages';
    protected static string $primaryKey = 'id';

    public function campaign()
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function gateway()
    {
        return $this->belongsTo(WhatsAppGateway::class, 'gateway_id');
    }
}
