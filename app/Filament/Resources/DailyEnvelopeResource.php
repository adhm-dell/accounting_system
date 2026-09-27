<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyEnvelopeResource\Pages;
use App\Models\DailyEnvelope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DailyEnvelopeResource extends Resource
{
    protected static ?string $model = DailyEnvelope::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'العمليات اليومية';
    protected static ?string $navigationLabel = 'الظروف اليومية';
    protected static ?string $modelLabel = 'ظرف يومي';
    protected static ?string $pluralModelLabel = 'الظروف اليومية';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الظرف')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('envelope_date')
                            ->label('تاريخ الظرف')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(now())
                            ->native(false)
                            ->displayFormat('Y-m-d'),

                        Forms\Components\Toggle::make('expenses_already_deducted')
                            ->label('المصاريف مخصومة مسبقاً؟')
                            ->helperText('فعّل إذا كانت المصاريف مخصومة من إجمالي الكاشير')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('المبالغ')
                    ->icon('heroicon-o-currency-dollar')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('cash_total')
                            ->label('إجمالي الكاش')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('ج.م')
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('network_total')
                            ->label('إجمالي الشبكة')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('ج.م')
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('expenses_total')
                            ->label('إجمالي المصاريف')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('ج.م')
                            ->live(onBlur: true)
                            ->hidden(fn(Forms\Get $get) => $get('expenses_already_deducted')),

                        Forms\Components\Select::make('expenses_paid_from')
                            ->label('المصاريف مدفوعة من')
                            ->options([
                                'cash' => 'الكاش',
                                'digital' => 'الشبكة',
                            ])
                            ->default('cash')
                            ->required()
                            ->live()
                            ->hidden(fn(Forms\Get $get) => $get('expenses_already_deducted')),
                    ]),

                Forms\Components\Section::make('المعاينة — سيُضاف للخزنة')
                    ->icon('heroicon-o-eye')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Placeholder::make('preview_cash')
                            ->label('🏦 صافي الكاش للخزنة')
                            ->content(function (Forms\Get $get) {
                                $cash = floatval($get('cash_total') ?? 0);
                                $expenses = floatval($get('expenses_total') ?? 0);
                                $deducted = $get('expenses_already_deducted');
                                $paidFrom = $get('expenses_paid_from') ?? 'cash';

                                if ($deducted) {
                                    return number_format($cash, 2) . ' ج.م';
                                }
                                if ($paidFrom === 'cash') {
                                    return number_format($cash - $expenses, 2) . ' ج.م';
                                }
                                return number_format($cash, 2) . ' ج.م';
                            }),

                        Forms\Components\Placeholder::make('preview_digital')
                            ->label('📡 صافي الشبكة')
                            ->content(function (Forms\Get $get) {
                                $network = floatval($get('network_total') ?? 0);
                                $expenses = floatval($get('expenses_total') ?? 0);
                                $deducted = $get('expenses_already_deducted');
                                $paidFrom = $get('expenses_paid_from') ?? 'cash';

                                if ($deducted) {
                                    return number_format($network, 2) . ' ج.م';
                                }
                                if ($paidFrom === 'digital') {
                                    return number_format($network - $expenses, 2) . ' ج.م';
                                }
                                return number_format($network, 2) . ' ج.م';
                            }),
                    ]),

                Forms\Components\Section::make('ملاحظات')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('envelope_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('envelope_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cash_total')
                    ->label('إجمالي الكاش')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('network_total')
                    ->label('إجمالي الشبكة')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expenses_total')
                    ->label('المصاريف')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('net_cash_to_safe')
                    ->label('صافي الكاش')
                    ->money('EGP')
                    ->sortable()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('net_digital')
                    ->label('صافي الشبكة')
                    ->money('EGP')
                    ->sortable()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('expenses_already_deducted')
                    ->label('مخصوم مسبقاً')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $date) => $q->where('envelope_date', '>=', $date))
                            ->when($data['until'], fn($q, $date) => $q->where('envelope_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyEnvelopes::route('/'),
            'create' => Pages\CreateDailyEnvelope::route('/create'),
            'edit' => Pages\EditDailyEnvelope::route('/{record}/edit'),
        ];
    }
}
