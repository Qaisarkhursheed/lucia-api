<?php

namespace App\ModelsExtended\Interfaces;

abstract class ShareableCategorizedFunctions
{
    /**
     * @param IShareableSortableInterface $a
     * @param IShareableSortableInterface $b
     * @return int
     */
    public static function compareTo(IShareableSortableInterface $a, IShareableSortableInterface $b ): int
    {
//        if ( $a->sortByKey()->equalTo( $b->sortByKey() ) ) {
//
//        }

        // compare day only without time
        if ( $a->sortByKey()->isSameDay( $b->sortByKey() ) ) {
            // second level sorting
            return $a->sorting_rank == $b->sorting_rank ? 0 : ($a->sorting_rank < $b->sorting_rank ? -1 : 1)  ;
        }
        return $a->sortByKey()->lessThan( $b->sortByKey() ) ? -1 : 1;
    }
}

