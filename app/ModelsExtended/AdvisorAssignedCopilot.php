<?php

namespace App\ModelsExtended;

/**
 * @property User $user
 */
class AdvisorAssignedCopilot extends \App\Models\AdvisorAssignedCopilot
{

    public function user()
    {
        return $this->belongsTo(User::class, 'copilot_id');
    }

}
