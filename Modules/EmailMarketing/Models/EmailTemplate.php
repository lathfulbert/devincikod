<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Traits\HasAuthor;

use App\Core\Database\Model;

class EmailTemplate extends Model
{
    use HasAuthor;

    protected static string $table = 'email_templates';

    protected array $fillable = [

        'name',
        'description',
        'subject',
        'type',
        'html_content',
        'json_structure',
        'thumbnail',
        'is_active',
        'is_default',
        'category',
        'tags',
        'created_by',
        'updated_by'
    ];

    /**
     * Relation avec les campagnes
     */
    public function campaigns()
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    /**
     * Remplacer les variables dans le template
     *
     * @param array $data ['first_name' => 'John', 'email' => 'john@example.com']
     * @return string HTML avec variables remplacées
     */
    public function render(array $data = []): string
    {
        $html = $this->html_content;

        // Remplacer les variables {{ variable }}
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }

        return $html;
    }

    /**
     * Remplacer le sujet avec variables
     */
    public function renderSubject(array $data = []): string
    {
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $subject = str_replace('{{ ' . $key . ' }}', $value, $subject);
        }

        return $subject;
    }

    /**
     * Extraire les variables du template
     *
     * @return array ['first_name', 'email', 'company']
     */
    public function getVariables(): array
    {
        preg_match_all('/\{\{\\s*([a-zA-Z0-9_]+)\\s*\}\}/', $this->html_content, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Cloner le template
     */
    public function duplicate(string $newName = null): self
    {
        $copy = $this->replicate();
        $copy->name = $newName ?? ($this->name . ' (Copy)');
        $copy->is_default = false;
        $copy->save();

        return $copy;
    }

    /**
     * Marquer comme template par défaut
     */
    public function setAsDefault(): void
    {
        // Retirer le défaut des autres templates de la même catégorie
        static::where('category', $this->category)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
