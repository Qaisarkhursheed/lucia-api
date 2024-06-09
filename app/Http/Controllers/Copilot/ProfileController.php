<?php

namespace App\Http\Controllers\Copilot;

class ProfileController extends \App\Http\Controllers\Agent\ProfileController
{
    public function update()
    {
        $this->validatedRules([
            'bio' => 'filled|string|min:1|max:5000',
            'timezone_offset_tzab' => 'filled|string|exists:db_timezone,offset_tzab',
            'city' => 'filled|string|max:100',
        ]);
        parent::update(); // TODO: Change the autogenerated stub

        if( \request()->has('bio') )
        {
            if(!isset($this->user->copilot_info))
            {
                // $this->user->copilot_info->bio = \request()->input('bio');
                // $this->user->copilot_info->update();

                $this->user->copilot_info()->create([
                // 'how_to_fulfill' => $request->input(  'how_to_fulfill' ),
                // 'free_time_recommendations' => $request->input(  'free_time_recommendations' ),
                // 'strengths' => $request->input(  'strengths' ),
                // 'weaknesses' => $request->input(  'weaknesses' ),
                // 'confidential_handling' => $request->input(  'confidential_handling' ),
                // 'experience' => $request->input(  'experience' ),
                // 'contact_references' => $request->input(  'contact_references' ),
                // 'other_info' => $request->input(  'other_info' ),
                'bio' => \request()->input('bio'),
                // 'resume_relative_url' => $request->hasFile('resume') ? CopilotInfo::saveImageOnCloud( $request->file(  'resume' ) , $user ) : null
            ]);

            }
            else{
                $this->user->copilot_info->bio = \request()->input('bio');
                $this->user->copilot_info->update();
            }
        }
        if( \request()->has('timezone_offset_tzab') )
            $this->user->preferred_timezone_tzab = \request()->input('timezone_offset_tzab');
        if( \request()->has('city') )
            $this->user->city = \request()->input('city');
            if( \request()->has('hourly_rate') )
            $this->user->hourly_rate = \request()->input('hourly_rate');

        $this->user->update();
        return $this->me();
    }
}
