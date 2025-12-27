<?php

namespace Modules\WhatsAppMarketing\Models;

use App\Core\Database\Model;

class WhatsAppTemplate extends Model
{
    protected static string $table = 'whatsapp_templates';
    protected static string $primaryKey = 'id';

    public function gateway()
    {
        return $this->belongsTo(WhatsAppGateway::class, 'gateway_id');
    }
}
