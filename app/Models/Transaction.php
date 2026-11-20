<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;
    public const EFFECTIVE_DATE = 'COALESCE(imported_transaction_date, DATE(created_at))';


    protected $fillable = [
        'user_id',
        'description',
        'amount',
        'type',
        'category',
        'currency',
        'original_amount',
        'original_currency',
        'payment_type',
        'billing_day',
        'imported_transaction_date',
    ];
    protected $appends = ['effective_date'];


    /**
     * Connection to user table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for dates
     */

    public function scopeBetweenDates(Builder $query, $from, $to): Builder
    {
        return $query->whereRaw(self::EFFECTIVE_DATE . ' BETWEEN ? AND ?', [
            Carbon::parse($from)->toDateString(),
            Carbon::parse($to)->toDateString(),
        ]);
    }

    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        $start = Carbon::create($year, $month, 1);

        return $query->betweenDates($start, $start->copy()->endOfMonth());
    }

    public function scopeOnDate(Builder $query, $date): Builder
    {
        return $query->betweenDates($date, $date);
    }

    protected function effectiveDate(): Attribute
    {
        return Attribute::get(fn() => Carbon::parse(
            $this->imported_transaction_date ?? $this->created_at
        )->toDateString());
    }

    public static function yearSql(): string
{
    return match (DB::connection()->getDriverName()) {
        'pgsql' => 'CAST(EXTRACT(YEAR FROM ' . self::EFFECTIVE_DATE . ') AS INTEGER)',
        'sqlite' => "CAST(strftime('%Y', " . self::EFFECTIVE_DATE . ') AS INTEGER)',
        default => 'YEAR(' . self::EFFECTIVE_DATE . ')',
    };
}

public static function monthKeySql(): string
{
    return match (DB::connection()->getDriverName()) {
        'pgsql' => "to_char(" . self::EFFECTIVE_DATE . ", 'YYYY-MM')",
        'sqlite' => "strftime('%Y-%m', " . self::EFFECTIVE_DATE . ')',
        default => "DATE_FORMAT(" . self::EFFECTIVE_DATE . ", '%Y-%m')",
    };
}
}