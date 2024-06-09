<?php

namespace App\ModelsExtended\Traits;

use App\ModelsExtended\Interfaces\IShareableSortableInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * @property string|null $custom_header_title
 */
trait ShareableSortablePackagerTrait
{
    /**
     * @inheritDoc
     */
    public function getBookingCategoryId(): int
    {
        return $this->booking_category_id;
    }

    /**
     * @inheritDoc
     */
    public function categorizeShareableAs(): string
    {
        return $this->booking_category->description;
    }


    public function packageAsSortableShareable():array
    {
        $array = $this->formatForSharing();
        $array["sortable_date"] = $this->sortByKey()->format( IShareableSortableInterface::SORT_DATE_FORMAT );
        $array["created_date_time"] = $this->created_at->toIso8601String( );

//        // deprecated
//        $array["category_booking_id"] = $this->id;
//        $array["booking_category_id"] = $this->booking_category_id;
//        // ------------------------------------------------------------

        $array["category"] = $this->booking_category->description;
        $array["guid"] = Str::uuid()->toString();
        $array["custom_header_title"] =  $this->title();

        $array["sorting_rank"] = $this->sorting_rank;
        $array["booking_id"] = $this->id;
        $array["category_id"] = $this->booking_category_id;

        return $array;
    }

    public function title(): ?string
    {
        // ?? coalesce feature fails if it is not null and if it is empty string.
        return  empty( $this->custom_header_title ) ? $this->getSupplierAttribute()->name : $this->custom_header_title;
    }

    public function notes(): ?string
    {
        return optional($this->getSupplierAttribute())->description;
    }

    public function presentForDev(): array
    {
        return $this->formatForSharing();
    }

    public function calendarStartDate(): Carbon
    {
        return $this->sortByKey();
    }

    public function calendarEndDate(): Carbon
    {
        return $this->sortByKey();
    }
}
