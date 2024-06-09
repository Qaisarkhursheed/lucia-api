<?php

namespace App\ModelsExtended;

trait LinkedSupplierPresentationTrait
{
    public function presentForDev(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "website" => $this->website,
            "address" => $this->address,
        ];
    }
}
