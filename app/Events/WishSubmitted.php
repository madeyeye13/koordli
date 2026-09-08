<?php
namespace App\Events;
use App\Models\Tenant\EventWish;
use Illuminate\Foundation\Events\Dispatchable;

class WishSubmitted { use Dispatchable; public function __construct(public EventWish $wish) {} }