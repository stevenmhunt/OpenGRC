<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'files' => 'array'
    ];


    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditItem(): BelongsTo
    {
        return $this->belongsTo(AuditItem::class);
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(DataRequestResponse::class);
    }

    public function attachments(): HasManyThrough
    {
        return $this->hasManyThrough(FileAttachment::class, DataRequestResponse::class);
    }

}
