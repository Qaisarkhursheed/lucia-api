<?php

namespace App\ModelsExtended;

use App\ModelsExtended\Interfaces\IDeveloperPresentationInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserNote extends \App\Models\UserNote implements IDeveloperPresentationInterface
{
    /**
     * @param string $title
     * @param int $created_by_id
     * @return Builder|Model|object|null|UserNote
     */
    public static function getSavedNote(string $title, int $created_by_id)
    {
        return self::query()
            ->where("title", $title)
            ->where("created_by_id", $created_by_id)
            ->first();
    }

    /**
     * @param string $title
     * @param int $created_by_id
     * @param string $notes
     * @return Builder|Model|UserNote
     */
    public static function addOrUpdate(string $title, int $created_by_id, string $notes)
    {
        return self::query()
            ->updateOrCreate([
                "title" => $title,
                "created_by_id" => $created_by_id
            ],
            [
                'notes' => $notes,
            ]);
    }

    public function presentForDev(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notes' => $this->notes,
        ];
    }
}
