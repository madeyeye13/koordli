<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistTemplateItem extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'checklist_template_id', 'title', 'description', 'phase', 'days_before_event', 'sort_order'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }
}