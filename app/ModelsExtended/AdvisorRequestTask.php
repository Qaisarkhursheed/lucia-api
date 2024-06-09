<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use App\Models\AdvisorTaskCategory;
use App\Models\Category;

class AdvisorRequestTask extends \App\Models\AdvisorRequestTask implements IDeveloperPresentationInterface
{

    public function presentForDev(): array
    {
        return  [
            'id' => $this->id,
            'explanation' => $this->explanation,
            //'advisor_request_type' => $this->advisor_request_type->description,
            //'advisor_request_type_id' => $this->advisor_request_type_id,
            'advisor_request_id' => $this->advisor_request_id,
            'completed' => $this->completed,
            'amount' => $this->amount,
            'title'=>$this->title,
            'categories' =>$this->advisor_request_task_categories,
        ];
    }

    public function requestCategories():array
    {
        return [$this->categories];
        //return  ['categories' =>!empty($this->categories)?$this->categories:null];
    }
    public function advisor_request_task_categories()
    {
        return $this->belongsToMany(Category::class, 'advisor_task_categories', 'advisor_request_task_id', 'category_id')
        ->withPivot('advisor_request_task_id', 'category_id')
        ->withTimestamps();
    }
    public function advisor_request_categories()
    {
        return $this->hasMany(\App\Models\AdvisorTaskCategory::class);
    }
}
