<?php

namespace Modules\WhatsAppMarketing\Models;

use App\Core\Database\Model;

class WhatsAppGateway extends Model
{
    protected static string $table = 'whatsapp_gateways';
    protected static string $primaryKey = 'id';

    public static function getDefault()
    {
        return self::where('is_active', 1)->first();
    }
}
