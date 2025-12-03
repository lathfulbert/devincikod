<?php

namespace Modules\EmailMarketing\Models;

use App\Core\Database\Model;

class Workflow extends Model
{
    protected static string $table = 'workflows';

    protected array $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'steps',
        'status',
        'total_executions',
        'successful_executions',
        'failed_executions',
        'created_by'
    ];

    /**
     * Relation avec les exécutions
     */
    public function executions()
    {
        return $this->hasMany(WorkflowExecution::class, 'workflow_id');
    }

    /**
     * Obtenir les steps décodés
     */
    public function getSteps(): array
    {
        return is_string($this->steps) ? json_decode($this->steps, true) : $this->steps;
    }

    /**
     * Activer le workflow
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Mettre en pause le workflow
     */
    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    /**
     * Archiver le workflow
     */
    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Incrémenter le compteur d'exécutions
     */
    public function incrementExecutions(bool $success = true): void
    {
        $this->increment('total_executions');

        if ($success) {
            $this->increment('successful_executions');
        } else {
            $this->increment('failed_executions');
        }
    }

    /**
     * Calculer le taux de succès
     */
    public function getSuccessRate(): float
    {
        if ($this->total_executions == 0) {
            return 0;
        }

        return round(($this->successful_executions / $this->total_executions) * 100, 2);
    }

    /**
     * Vérifier si le workflow est actif
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
