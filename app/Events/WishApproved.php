<?php
namespace App\Events;
use App\Models\Tenant\EventWish;
use Illuminate\Foundation\Events\Dispatchable;

class WishApproved { use Dispatchable; public function __construct(public EventWish $wish) {} }