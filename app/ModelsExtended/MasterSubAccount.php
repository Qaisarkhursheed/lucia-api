<?php

namespace App\ModelsExtended;

/**
 * @property User $user
 */
class MasterSubAccount extends \App\Models\MasterSubAccount
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsAccountOwnerAttribute()
    {
        return $this->user_id === $this->master_account->user_id;
    }
}