<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Model;

class WorkflowExecution extends Model
{
    protected static string $table = 'workflow_executions';

    protected array $fillable = [
        'workflow_id',
        'contact_id',
        'status',
        'current_step',
        'total_steps',
        'started_at',
        'completed_at',
        'next_step_at',
        'execution_data',
        'error'
    ];

    /**
     * Relation avec le workflow
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    /**
     * Démarrer l'exécution
     */
    public function start(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Passer à l'étape suivante
     */
    public function nextStep(int $delaySeconds = 0): void
    {
        $nextStep = $this->current_step + 1;

        $data = ['current_step' => $nextStep];

        if ($delaySeconds > 0) {
            $data['next_step_at'] = date('Y-m-d H:i:s', time() + $delaySeconds);
        }

        $this->update($data);
    }

    /**
     * Marquer comme complétée
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        // Incrémenter le compteur du workflow
        $this->workflow->incrementExecutions(true);
    }

    /**
     * Marquer comme échouée
     */
    public function fail(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error
        ]);

        // Incrémenter le compteur du workflow
        $this->workflow->incrementExecutions(false);
    }

    /**
     * Annuler l'exécution
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Vérifier si l'exécution est prête pour la prochaine étape
     */
    public function isReadyForNextStep(): bool
    {
        if (!$this->next_step_at) {
            return true;
        }

        return strtotime($this->next_step_at) <= time();
    }

    /**
     * Obtenir les données d'exécution décodées
     */
    public function getExecutionData(): array
    {
        return is_string($this->execution_data)
            ? json_decode($this->execution_data, true)
            : ($this->execution_data ?? []);
    }

    /**
     * Ajouter des données d'exécution
     */
    public function addExecutionData(array $data): void
    {
        $current = $this->getExecutionData();
        $updated = array_merge($current, $data);

        $this->update(['execution_data' => json_encode($updated)]);
    }
}
