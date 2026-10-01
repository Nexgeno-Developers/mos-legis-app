<?php

namespace App\Socialite;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Orcid\Provider;
use Throwable;

/**
 * ORCID sign-in (SOW B.01) on top of the SocialiteProviders driver, hardened:
 * - requests only /authenticate (Public API; /read-limited needs Member API access);
 * - reads the public record from the current v3.0 API instead of the deprecated v2.1;
 * - never fails sign-in because the record is private or has no email list — the
 *   iD and name returned with the access token are always enough to identify the user.
 */
class OrcidProvider extends Provider
{
    protected $scopes = ['/authenticate'];

    protected function getUserByToken($token)
    {
        $orcid = Arr::get($token, 'orcid');
        $record = [];

        try {
            $response = $this->getHttpClient()->get($this->recordUrl($orcid), [
                RequestOptions::HEADERS => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.Arr::get($token, 'access_token'),
                ],
                RequestOptions::TIMEOUT => 10,
            ]);
            $record = json_decode((string) $response->getBody(), true) ?: [];
        } catch (Throwable $e) {
            Log::warning('ORCID public record could not be read; continuing with token data.', ['orcid' => $orcid, 'error' => $e->getMessage()]);
        }

        return [
            'orcid' => $orcid,
            'token_name' => Arr::get($token, 'name'),
            'record' => $record,
        ];
    }

    protected function mapUserToObject(array $user)
    {
        $record = $user['record'] ?? [];
        $given = Arr::get($record, 'person.name.given-names.value');
        $family = Arr::get($record, 'person.name.family-name.value');
        $name = trim(($given ?? '').' '.($family ?? '')) ?: ($user['token_name'] ?? null);

        return (new User)->setRaw($user)->map([
            'id' => Arr::get($record, 'orcid-identifier.path', $user['orcid']),
            'nickname' => $given,
            'name' => $name,
            'email' => $this->verifiedEmail($record),
        ]);
    }

    /** Primary verified email, else any verified email; ORCID users may keep email private. */
    private function verifiedEmail(array $record): ?string
    {
        $emails = collect(Arr::get($record, 'person.emails.email') ?? [])->filter(fn ($e) => ($e['verified'] ?? false) === true);

        return ($emails->firstWhere('primary', true) ?? $emails->first())['email'] ?? null;
    }

    private function recordUrl(string $orcid): string
    {
        $base = $this->getConfig('environment') === 'production' ? 'https://pub.orcid.org/' : 'https://pub.sandbox.orcid.org/';

        return "{$base}v3.0/{$orcid}/record";
    }
}
