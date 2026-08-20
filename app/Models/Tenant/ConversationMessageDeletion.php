<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ConversationMessageDeletion extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'message_id', 'participant_type', 'participant_id'];
}