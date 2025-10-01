<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'slug',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';
    public const ROLE_VIEWER = 'viewer';

    public static function roles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
            self::ROLE_MEMBER,
            self::ROLE_VIEWER,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class)->orderBy('position');
    }

    public function lists(): HasManyThrough
    {
        return $this->hasManyThrough(BoardList::class, Board::class)->orderBy('position');
    }

    public function cards(): HasManyThrough
    {
        return $this->hasManyThrough(Card::class, BoardList::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function roleFor(User $user): ?string
    {
        if ($user->is($this->owner)) {
            return self::ROLE_OWNER;
        }

        return $this->members->firstWhere('id', $user->id)?->pivot?->role;
    }

    public function canManage(User $user): bool
    {
        $role = $this->roleFor($user);

        return in_array($role, [self::ROLE_OWNER, self::ROLE_ADMIN], true);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where('owner_id', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
    }
}
