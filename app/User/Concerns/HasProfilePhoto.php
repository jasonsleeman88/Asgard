<?php

declare(strict_types=1);

namespace App\User\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $profile_photo_path
 * @property string $profile_photo_url
 */
trait HasProfilePhoto
{
    public function initializeHasProfilePhoto(): void
    {
        $this->append('profile_photo_url');
    }

    public function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            return $this->profile_photo_path
                ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                : $this->defaultProfilePhotoUrl();
        });
    }

    public function defaultProfilePhotoUrl(): string
    {
        return radiance()
            ->seed($this->email)
            ->text($this->initials())
            ->size(256)
            ->fontFamily('Inter')
            ->enablePixelPattern()
            ->pixelShapeMix()
            ->toBase64();
    }

    public function profilePhotoDisk(): string
    {
        return 'public';
    }
}
