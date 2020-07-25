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

    /**
     * Check if the authenticatable instance
     * has a Social account.
     *
     * @param  string  $provider
     * @return bool
     */
    public function hasSocial(string $provider): bool
    {
        return $this->socials()
            ->whereProvider($provider)
            ->exists();
    }

    /**
     * Get the social account for a specific provider.
     *
     * @param  string  $provider
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getSocial(string $provider)
    {
        return $this->socials()
            ->whereProvider($provider)
            ->first();
    }
}
