<?php

namespace Modules\Wallet\Models;

use App\Core\Database\Model;

class WalletTopupRequest extends Model
{
    protected static string $table = 'wallet_topup_requests';

    protected array $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'currency',
        'payment_method',
        'gateway_id',
        'gateway_transaction_id',
        'gateway_response',
        'gateway_status',
        'status',
        'notes',
        'proof_of_payment',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'ip_address',
        'user_agent',
        'completed_at'
    ];

    protected array $casts = [
        'amount' => 'float',
        'gateway_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Get the user who made the request
     */
    public function user()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'user_id');
    }

    /**
     * Get the wallet
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Get the payment gateway
     */
    public function gateway()
    {
        return $this->belongsTo(\Modules\Settings\Models\WalletGateway::class, 'gateway_id');
    }

    /**
     * Get the admin who reviewed the request
     */
    public function reviewer()
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'reviewed_by');
    }

    /**
     * Check if the request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the request requires admin approval
     */
    public function requiresApproval(): bool
    {
        return in_array($this->payment_method, ['cash', 'mobile_money', 'bank_transfer', 'other']);
    }

    /**
     * Check if the request is paid via gateway
     */
    public function isGatewayPayment(): bool
    {
        return $this->payment_method === 'gateway';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'approved' => 'success',
            'completed' => 'success',
            'rejected' => 'danger',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'approved' => 'Approuvée',
            'completed' => 'Complétée',
            'rejected' => 'Rejetée',
            'failed' => 'Échouée',
            'cancelled' => 'Annulée',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'gateway' => 'Passerelle de paiement',
            'cash' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Virement bancaire',
            'other' => 'Autre',
            default => ucfirst($this->payment_method)
        };
    }

    /**
     * Mark as approved
     */
    public function markAsApproved(int $reviewerId, ?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $adminNotes
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected(int $reviewerId, ?string $adminNotes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $adminNotes
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'admin_notes' => $reason
        ]);
    }
}
