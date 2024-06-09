<?php

namespace App\ModelsExtended;

use App\Models\ViewLatestClientEmail;
use App\ModelsExtended\Interfaces\IGlobalSearchableInterface;

/**
 * @property string|null $latest_email
 */
class ItineraryClient extends \App\Models\ItineraryClient
    implements IGlobalSearchableInterface
{
    public function latest_email()
    {
        return $this->hasOne(ViewLatestClientEmail::class, 'itinerary_client_id' );
    }

    /**
     * @inheritDoc
     */
    public function globalSearchResultView(): array
    {
        return array_merge(
            $this->only( [ 'name', 'phone', 'id', 'itinerary_id' ] ),
            [
                'email' => optional( $this->latest_email )->email,
            ]
        );
    }
}
