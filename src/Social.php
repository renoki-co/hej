<?php

namespace RenokiCo\Hej;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * Get the model that uses this Social instance.
     *
     * @return mixed
     */
    public function model()
    {
        return $this->morphTo();
    }
}
