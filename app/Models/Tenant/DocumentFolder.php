<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentFolder extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'documentable_type', 'documentable_id',
        'name', 'created_by_type', 'created_by_id',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'folder_id');
    }
}