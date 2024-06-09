<?php

namespace App\ModelsExtended\Interfaces;


use Illuminate\Database\Eloquent\Model;

interface IReplicableEloquent
{
    /**
     * Returns a duplicated version.
     * NB: This version is not saved. It only creates it in memory
     *
     * @return Model | IReplicableEloquent
     */
    public function duplicateWithRelations():Model;

    /**
     * Returns the model saved
     *
     * @return Model | IReplicableEloquent
     */
    public function saveWithRelations():Model;

    /**
     * Returns the model saved
     *
     * @param Model $parentModel
     * @param string $parentRelationName
     * @return Model|IReplicableEloquent
     */
    public function saveChildWithRelations(Model $parentModel, string $parentRelationName ):Model;

    /**
     * Calls this method while replicating
     * Returns the newModel
     *
     * @param Model $oldModel
     * @param Model $newModel
     * @return Model
     */
    public function onReplicating(Model $oldModel, Model $newModel):Model;

    /**
     * Called after the new model has been saved
     * Returns the newModel Saved
     *
     * @return Model
     */
    public function onSavedReplication():Model;
}
