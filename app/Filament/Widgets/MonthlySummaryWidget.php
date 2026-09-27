<?php

namespace App\Filament\Widgets;

use App\Models\CapitalExpense;
use App\Models\OwnerWithdrawal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class MonthlySummaryWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalWithdrawals = OwnerWithdrawal::whereBetween('withdrawal_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalExpenses = CapitalExpense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return [
            Stat::make('مسحوبات الشهر', number_format($totalWithdrawals, 2) . ' ج.م')
                ->description('إجمالي مسحوبات الشركاء')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning')
                ->icon('heroicon-o-arrow-up-tray'),

            Stat::make('مصاريف إنشائية', number_format($totalExpenses, 2) . ' ج.م')
                ->description('إجمالي المصاريف الإنشائية')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger')
                ->icon('heroicon-o-receipt-percent'),
        ];
    }
}
