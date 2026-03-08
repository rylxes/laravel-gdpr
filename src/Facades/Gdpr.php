<?php

namespace Rylxes\Gdpr\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Rylxes\Gdpr\Models\DataExport export(\Illuminate\Database\Eloquent\Model $user, ?string $format = null)
 * @method static \Rylxes\Gdpr\Models\ErasureRequest erase(\Illuminate\Database\Eloquent\Model $user, ?string $strategy = null, ?string $reason = null, ?string $requestedBy = null)
 * @method static \Rylxes\Gdpr\Models\ConsentLog recordConsent(\Illuminate\Database\Eloquent\Model $user, string $type, ?string $ipAddress = null, ?string $version = null, array $metadata = [])
 * @method static int revokeConsent(\Illuminate\Database\Eloquent\Model $user, string $type)
 * @method static bool hasConsent(\Illuminate\Database\Eloquent\Model $user, string $type)
 * @method static array discoverExportables(\Illuminate\Database\Eloquent\Model $user)
 * @method static array discoverDeletables(\Illuminate\Database\Eloquent\Model $user)
 * @method static \Rylxes\Gdpr\Support\DependencyResolver dependencyResolver()
 * @method static \Rylxes\Gdpr\Support\DataPackager packager()
 * @method static \Rylxes\Gdpr\Support\DownloadLinkGenerator downloadLinks()
 *
 * @see \Rylxes\Gdpr\GdprManager
 */
class Gdpr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'gdpr';
    }
}
