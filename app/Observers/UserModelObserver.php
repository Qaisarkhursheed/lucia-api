<?php

namespace App\Observers;

use App\ModelsExtended\User;
use Illuminate\Support\Facades\Artisan;

class UserModelObserver
{
    public function created( User $user )
    {
        // at the point of creation, there won't be role
//       if( $user->isTravelAgent() || $user->isCopilot() )
//           Artisan::call( 'payments:setup-customer ' . $user->id );
    }

    // Both creating and updating methods calls saving
//    public function updating( User $user )
//    {
//        info( 'User observer under updating');
//        $this->manipulateBeforeSave( $user );
//    }

    /**
     * Perform Manipulations
     * @param User $user
     */
    public function saving( User $user )
    {
//        info('User observer under saving');
        $this->manipulateBeforeSave($user);

        // if this returns false, it will cancel update with no error
        // also return void is counted as true
        //  return true;
    }

    /**
     * Perform Manipulations
     *
     * @param User $user
     */
    public function manipulateBeforeSave(User $user)
    {
        $user->name = $user->first_name . ' ' . $user->last_name;
        if( $user->address_line1 || $user->address_line2 )
        {
            $user->location = ($user->address_line1??'') . ' ' . ($user->address_line2??'');
            $user->location = trim($user->location);
        }
    }
}
