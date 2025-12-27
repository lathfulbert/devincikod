<?php

namespace Modules\WhatsAppMarketing\Models;

use App\Core\Database\Model;
use Modules\Contacts\Models\Contact;

class WhatsAppContact extends Model
{
    protected static string $table = 'whatsapp_contacts';
    protected static string $primaryKey = 'id';

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
