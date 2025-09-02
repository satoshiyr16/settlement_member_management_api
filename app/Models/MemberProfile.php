<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Billable;

class MemberProfile extends Model
{
    use HasFactory, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nickname',
        'gender',
        'birth_date',
        'enrollment_date',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
            'enrollment_date' => 'date:Y-m-d',
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * ユーザーとのリレーション
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
