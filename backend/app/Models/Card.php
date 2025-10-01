<?php

namespace App\Models;

use App\Models\CardAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_list_id',
        'title',
        'description',
        'position',
        'due_at',
        'archived',
    ];

    protected $casts = [
        'position' => 'integer',
        'due_at' => 'datetime',
        'archived' => 'boolean',
    ];

    public function boardList(): BelongsTo
    {
        return $this->belongsTo(BoardList::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CardAttachment::class);
    }
}
