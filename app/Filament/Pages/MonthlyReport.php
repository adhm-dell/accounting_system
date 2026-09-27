<?php

namespace App\Filament\Pages;

use App\Models\CapitalExpense;
use App\Models\DailyEnvelope;
use App\Models\DebtPayment;
use App\Models\OwnerWithdrawal;
use App\Services\SafeBalanceService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class MonthlyReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'الخزنة والتقارير';
    protected static ?string $navigationLabel = 'التقرير الشهري';
    protected static ?string $title = 'التقرير الشهري';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.monthly-report';

    public ?int $selectedMonth = null;
    public ?int $selectedYear = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('selectedMonth')
                        ->label('الشهر')
                        ->options([
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
                        ])
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('selectedYear')
                        ->label('السنة')
                        ->options(function () {
                            $years = [];
                            for ($y = 2024; $y <= now()->year + 1; $y++) {
                                $years[$y] = $y;
                            }
                            return $years;
                        })
                        ->required()
                        ->live(),
                ]),
            ]);
    }

    public function getReportData(): array
    {
        $start = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $service = app(SafeBalanceService::class);

        // Deposits
        $totalCashDeposits = DailyEnvelope::whereBetween('envelope_date', [$start, $end])->sum('net_cash_to_safe');
        $totalDigitalDeposits = DailyEnvelope::whereBetween('envelope_date', [$start, $end])->sum('net_digital');

        // Withdrawals per owner
        $withdrawalsByOwner = OwnerWithdrawal::with('owner')
            ->whereBetween('withdrawal_date', [$start, $end])
            ->get()
            ->groupBy('owner_id')
            ->map(fn($items) => [
                'name' => $items->first()->owner->name ?? 'غير محدد',
                'total' => $items->sum('amount'),
            ])
            ->values()
            ->toArray();

        $totalWithdrawals = array_sum(array_column($withdrawalsByOwner, 'total'));

        // Capital expenses by category
        $expensesByCategory = CapitalExpense::whereBetween('expense_date', [$start, $end])
            ->get()
            ->groupBy('category')
            ->map(fn($items, $cat) => [
                'label' => CapitalExpense::categoryLabels()[$cat] ?? $cat,
                'total' => $items->sum('amount'),
            ])
            ->values()
            ->toArray();

        $totalExpenses = array_sum(array_column($expensesByCategory, 'total'));

        // Debt payments
        $totalDebtPayments = DebtPayment::whereBetween('payment_date', [$start, $end])->sum('amount');

        // Opening & closing balance
        $openingCash = $service->getBalanceAsOf($start->copy()->subDay(), 'cash');
        $openingDigital = $service->getBalanceAsOf($start->copy()->subDay(), 'digital');
        $closingCash = $service->getBalanceAsOf($end, 'cash');
        $closingDigital = $service->getBalanceAsOf($end, 'digital');

        return [
            'totalCashDeposits' => $totalCashDeposits,
            'totalDigitalDeposits' => $totalDigitalDeposits,
            'withdrawalsByOwner' => $withdrawalsByOwner,
            'totalWithdrawals' => $totalWithdrawals,
            'expensesByCategory' => $expensesByCategory,
            'totalExpenses' => $totalExpenses,
            'totalDebtPayments' => $totalDebtPayments,
            'openingCash' => $openingCash,
            'openingDigital' => $openingDigital,
            'closingCash' => $closingCash,
            'closingDigital' => $closingDigital,
        ];
    }
}
