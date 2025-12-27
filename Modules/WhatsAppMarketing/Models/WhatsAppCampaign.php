<?php

namespace Modules\WhatsAppMarketing\Models;

use App\Core\Database\Model;

class WhatsAppCampaign extends Model
{
    protected static string $table = 'whatsapp_campaigns';
    protected static string $primaryKey = 'id';

    public function template()
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    public function messages()
    {
        return $this->hasMany(WhatsAppMessage::class, 'campaign_id');
    }

    public function incrementSent()
    {
        // Custom query to atomic update if needed, but simple update for now
        $this->total_sent++;
        $this->save();
    }
}
