<?php

namespace App\Models;

use App\Exceptions\UserBlockedException;
use App\Notifications\UserBlockedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;

/**
 * @property integer id
 * @property string name
 * @property string email
 * @property Carbon email_verified_at
 * @property string password
 * @property string remember_token
 * @property boolean is_blocked
 * @property integer phone
 * @property string telegram
 * @property boolean duo
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 *
 * @method static whereHas(string $string, \Closure $param)
 */
class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_blocked',
        'telegram',
        'twoFactorMode',
        'phone',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function extension(): HasOne
    {
        return $this->hasOne(GoogleExtension::class, 'user_id');
    }

    public function telegram(): HasOne
    {
        return $this->hasOne(TelegramBot::class, 'user_id');
    }

    /**
     * @throws UserBlockedException
     */
    public function findForPassport($username)
    {
        $user = User::where('email', $username)
            ->first();
        if (!empty($user) && $user->is_blocked) {
            throw  new UserBlockedException('Пользователь заблокирован', 6, 'user_blocked', 429);
        }
        return $user;
    }

    public function block(): ?User
    {
        $this->is_blocked = true;
        $this->save();
        $this->notify(new UserBlockedNotification());
        return $this->fresh();
    }

    public function projects()
    {
        return Project::orWhere([
            'pm_id' => $this->id,
            'assessor_id' => $this->id,
            'senior_id' => $this->id,
        ])->get();
    }

    public function routeNotificationForTelegram(): ?int
    {
        return (int)$this->telegram()->value('chat_id');
    }

    public function routeNotificationForSmsRu(): ?string
    {
        return $this->phone;
    }

    public function getIsSmsCodeCheckedAttribute()
    {
        return SmsCodeHistory::where('user_email', $this->email)->where('is_current', true)->value('status');
    }

    public static function admins()
    {
        return User::whereHas('roles', function (Builder $query) {
            $query->where('roles.name', '=', 'sa');
        })->get();
    }

    public static function technicalSupports()
    {
        return User::whereHas('roles', function (Builder $query) {
            $query->where('roles.name', '=', 'technical_support');
        })->get();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('sa');
    }

    public function isSeniorAssessor(): bool
    {
        return $this->hasRole('senior_assessor');
    }

    public function isAnalytic(): bool
    {
        return $this->hasRole('analytic');
    }

    public function isPm(): bool
    {
        return $this->hasRole('pm');
    }

    public function isAssessor(): bool
    {
        return $this->hasRole('assessor');
    }


    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }
}

/**
 *  @OA\Schema(
 *      schema="User",
 *      @OA\Property(property="id", type="integer", format="int32"),
 *      @OA\Property(property="name", type="string"),
 *      @OA\Property(property="email", type="string"),
 *      @OA\Property(property="role", type="string"),
 *      @OA\Property(property="role_name", type="string")
 *  )
 */
