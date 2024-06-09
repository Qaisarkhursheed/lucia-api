<?php

namespace App\ModelsExtended\Traits;

use App\ModelsExtended\Interfaces\IReplicableEloquent;
use App\ModelsExtended\Interfaces\IShareableRenderInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property array|string[] $doNotReplicateProperties Indicate properties that should be excluded during replication
 * @property array|string[] $replicableRelations Indicate the relations name that implement IReplicableEloquent
 */
trait ReplicableEloquentTrait
{
    /**
     * @inheritDoc
     *
     * @return Model|ReplicableEloquentTrait
     */
    public function duplicateWithRelations():Model{
        $model = $this->onReplicating( $this, $this->replicate($this->doNotReplicateProperties) );

        if( $this->replicableRelations )
            foreach ( $this->replicableRelations as $relationName )
            {
                // eager load if not loaded
                if( !  $this->relationLoaded( $relationName ) ) $this->load( $relationName );
                $relationOrRelationCollection = $this->getRelation( $relationName );
                if( $relationOrRelationCollection instanceof Collection)
                    $model->{$relationName} =
                        $relationOrRelationCollection->map(
                            function (IReplicableEloquent $relationObject )use( $model, $relationName ){
                                return $relationObject->duplicateWithRelations();
                        });
                else
                    $model->{$relationName} = optional($relationOrRelationCollection)->duplicateWithRelations();
            }
        return $model;
    }

    /**
     * @return Model|ReplicableEloquentTrait|IShareableRenderInterface
     */
    public function saveWithRelations():Model
    {
        return DB::transaction(function (){ return $this->saveWithRelationsCore( ); });
    }

    /**
     * It returns the new model created
     *
     * @param Model $parentModel
     * @param string $parentRelationName
     * @return Model
     */
    public function saveChildWithRelations(Model $parentModel, string $parentRelationName ):Model
    {
        return  $this->saveWithRelationsCore($parentModel, $parentRelationName);
    }

    /**
     * It returns the new model created
     *
     * @param Model|null $parentModel
     * @param string|null $parentRelationName
     * @return Model
     */
    public function saveWithRelationsCore(?Model $parentModel = null, ?string $parentRelationName = null):Model
    {
        if( $parentModel )
        {
            $myModel = $parentModel->{$parentRelationName}()->create( $this->toArray() );
        }else{
            $myModel = self::create($this->toArray());
        }

        if( $this->replicableRelations )
            foreach ( $this->replicableRelations as $relationName )
            {
                $relationOrRelationCollection = $this->{$relationName};
                if( $relationOrRelationCollection instanceof Collection)
                {
                    $myModel->{$relationName} =
                        $relationOrRelationCollection->map( function ( IReplicableEloquent $relationObject )use( $myModel, $relationName ){
                            return $relationObject
                                ->saveChildWithRelations( $myModel, $relationName )
                                ->onSavedReplication();
                        } );
                }
                else
                {
//                    $myModel->{$relationName} = $relationOrRelationCollection
//                        ->saveChildWithRelations( $myModel, $relationName )
//                        ->onSavedReplication();

                    // this is better, it prevents error if the replicable item is null on parent
                    $myModel->{$relationName} = $relationOrRelationCollection;
                    if( $myModel->{$relationName} )
                    {
                        $myModel->{$relationName}->saveChildWithRelations( $myModel, $relationName )
                            ->onSavedReplication();
                    }
                }
            }

        return $myModel;
    }

    /**
     * @inheritDoc
     */
    public function onReplicating(Model $oldModel, Model $newModel):Model{
        // perform extra actions before saving
        return $newModel;
    }

    /**
     * @inheritDoc
     */
    public function onSavedReplication():Model{
        // perform extra actions after saved
        return $this;
    }
}
