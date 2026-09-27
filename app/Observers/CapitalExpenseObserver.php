<?php

namespace App\Observers;

use App\Models\CapitalExpense;
use App\Models\SafeTransaction;
use App\Services\SafeBalanceService;

class CapitalExpenseObserver
{
    public function __construct(
        protected SafeBalanceService $balanceService,
    ) {}

    public function created(CapitalExpense $expense): void
    {
        $snapshot = $this->balanceService->getBalanceSnapshot();

        SafeTransaction::create([
            'transaction_date' => $expense->expense_date,
            'type' => 'capital_expense',
            'source' => $expense->source,
            'direction' => 'out',
            'amount' => $expense->amount,
            'reference_type' => CapitalExpense::class,
            'reference_id' => $expense->id,
            'balance_after_cash' => $snapshot['cash'],
            'balance_after_digital' => $snapshot['digital'],
        ]);
    }
}
