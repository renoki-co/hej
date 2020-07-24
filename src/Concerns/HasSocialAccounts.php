<?php

namespace RenokiCo\Hej\Concerns;

trait HasSocialAccounts
{
    /**
     * Get the social accounts for this model.
     *
     * @return mixed
     */
    public function socials()
    {
        return $this->morphMany(config('hej.models.social'), 'model');
    }
}
