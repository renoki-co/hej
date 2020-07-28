<?php

namespace RenokiCo\Hej;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'model_id', 'model_type', 'provider', 'provider_id',
        'provider_nickname', 'provider_name', 'provider_email',
        'provider_avatar',
    ];

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
