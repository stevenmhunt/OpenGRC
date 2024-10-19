<?php

namespace App\Models;

use App\Enums\Applicability;
use App\Enums\ControlCategory;
use App\Enums\ControlEnforcementCategory;
use App\Enums\ControlType;
use App\Enums\Effectiveness;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class Control
 *
 * @package App\Models
 * @property int $id
 * @property Applicability $status
 * @property Effectiveness $effectiveness
 * @property ControlType $type
 * @property ControlCategory $category
 * @property ControlEnforcementCategory $enforcement
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Standard $standard
 * @property-read Collection|Implementation[] $implementations
 * @property-read int|null $implementations_count
 * @property-read Collection|AuditItem[] $auditItems
 * @property-read int|null $auditItems_count
 * @property-read Collection|AuditItem[] $completedAuditItems
 * @property-read int|null $completedAuditItems_count
 * @method static Builder|Control newModelQuery()
 * @method static Builder|Control newQuery()
 * @method static \Illuminate\Database\Query\Builder|Control onlyTrashed()
 * @method static Builder|Control query()
 * @method static Builder|Control whereCreatedAt($value)
 * @method static Builder|Control whereDeletedAt($value)
 * @method static Builder|Control whereId($value)
 * @method static Builder|Control whereStatus($value)
 * @method static Builder|Control whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Control withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Control withoutTrashed()
 * @mixin Eloquent
 */
class Control extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Indicates if the model should be indexed as you type.
     *
     * @var bool
     */
    public $asYouType = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'status' => Applicability::class,
        'effectiveness' => Effectiveness::class,
        'type' => ControlType::class,
        'category' => ControlCategory::class,
        'enforcement' => ControlEnforcementCategory::class,
    ];

    /**
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'controls_index';
    }

    /**
     * Get the array representation of the model for search.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();
        return $array;
    }

    /**
     * Get the standard that owns the control.
     *
     * @return BelongsTo
     */
    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    /**
     * The implementations that belong to the control.
     *
     * @return BelongsToMany
     */
    public function implementations(): BelongsToMany
    {
        return $this->belongsToMany(Implementation::class)
            ->withTimestamps();
    }

    /**
     * Get the audit items for the control.
     *
     * @return HasMany
     */
    public function auditItems(): HasMany
    {
        return $this->hasMany(AuditItem::class);
    }

    /**
     * Get the effectiveness of the control.
     *
     * @return Effectiveness
     */
    public function getEffectiveness(): Effectiveness
    {
        $latestAuditItem = $this->latestCompletedAuditItem();
        return $latestAuditItem ? $latestAuditItem->effectiveness : Effectiveness::UNKNOWN;
    }

    /**
     * Get the completed audit items for the control.
     *
     * @return MorphMany
     */
    public function completedAuditItems(): MorphMany
    {
        return $this->audits()->where('status', '=', 'Completed');
    }

    public function latestCompletedAuditItem(): ?AuditItem
    {
        return $this->completedAuditItems()->latest()->first();
    }

    /**
     * Get all the audit items for the control.
     *
     * @return MorphMany
     */
    public function audits(): MorphMany
    {
        return $this->morphMany(AuditItem::class, 'auditable');
    }

    /**
     * Get the date of the last effectiveness update.
     *
     * @return string
     */
    public function getEffectivenessDate(): string
    {
        $latestAuditItem = $this->latestCompletedAuditItem();
        return $latestAuditItem ? $latestAuditItem->updated_at->isoFormat('MMM D, YYYY') : "Never";
    }

}