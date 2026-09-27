<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DailyEnvelope extends Model
{
    protected $fillable = [
        'envelope_date',
        'cash_total',
        'network_total',
        'expenses_total',
        'expenses_paid_from',
        'expenses_already_deducted',
        'net_cash_to_safe',
        'net_digital',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'envelope_date' => 'date',
            'cash_total' => 'decimal:2',
            'network_total' => 'decimal:2',
            'expenses_total' => 'decimal:2',
            'net_cash_to_safe' => 'decimal:2',
            'net_digital' => 'decimal:2',
            'expenses_already_deducted' => 'boolean',
        ];
    }

    // ── Boot: auto-compute net values ──
    protected static function booted(): void
    {
        static::saving(function (DailyEnvelope $envelope) {
            $envelope->computeNetValues();
        });
    }

    public function computeNetValues(): void
    {
        if ($this->expenses_already_deducted) {
            // Expenses already deducted from the offline POS totals
            $this->net_cash_to_safe = $this->cash_total;
            $this->net_digital = $this->network_total;
        } else {
            if ($this->expenses_paid_from === 'cash') {
                $this->net_cash_to_safe = $this->cash_total - $this->expenses_total;
                $this->net_digital = $this->network_total;
            } else {
                $this->net_cash_to_safe = $this->cash_total;
                $this->net_digital = $this->network_total - $this->expenses_total;
            }
        }
    }

    // ── Relations ──
    public function safeTransactions(): MorphMany
    {
        return $this->morphMany(SafeTransaction::class, 'reference');
    }
}
