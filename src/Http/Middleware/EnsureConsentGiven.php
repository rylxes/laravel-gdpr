<?php

namespace Rylxes\Gdpr\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rylxes\Gdpr\Models\ConsentLog;
use Symfony\Component\HttpFoundation\Response;

class EnsureConsentGiven
{
    /**
     * Handle an incoming request.
     *
     * Usage: Route::middleware('gdpr.consent:marketing,analytics')
     *
     * @param string ...$consentTypes Required consent types
     */
    public function handle(Request $request, Closure $next, string ...$consentTypes): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        foreach ($consentTypes as $type) {
            $hasConsent = ConsentLog::where('user_id', $user->getKey())
                ->where('user_type', get_class($user))
                ->where('consent_type', $type)
                ->whereNull('revoked_at')
                ->exists();

            if (! $hasConsent) {
                abort(403, "Consent required: {$type}");
            }
        }

        return $next($request);
    }
}
