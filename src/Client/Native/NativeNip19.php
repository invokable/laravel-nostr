<?php

declare(strict_types=1);

namespace Revolution\Nostr\Client\Native;

use BitWasp\Bech32\Exception\Bech32Exception;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Nostr\Client\Native\Concerns\HasHttp;
use Revolution\Nostr\Nip19\AddressPointer;
use Revolution\Nostr\Nip19\EventPointer;
use Revolution\Nostr\Nip19\ProfilePointer;
use swentel\nostr\Event\Event;
use swentel\nostr\Event\Profile\Profile;
use swentel\nostr\Nip19\Nip19Helper;

class NativeNip19
{
    use Conditionable;
    use HasHttp;
    use Macroable;

    /**
     * Decode NIP-19 string.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->nip19()->decode('npub1...');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'type' => 'npub',
     * //     'data' => 'hex-encoded-string-or-structured-array',
     * // ]
     * ```
     *
     * @param  string  $n  nsec, npub, note, nprofile, nevent, naddr
     * @return \Illuminate\Http\Client\Response JSON: {type: string, data: mixed}
     *
     * @throws Exception
     * @throws BindingResolutionException
     */
    public function decode(string $n): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);
        $decoded = $nip19->decode($n);

        // Transform the Nip19Helper output to match NodeNip19 (Vercel API) format
        // NodeNip19 returns {type: string, data: mixed} format
        $transformed = $this->transformDecodeResult($decoded);

        return $this->response($transformed);
    }

    /**
     * Transform Nip19Helper decode result to NodeNip19-compatible format.
     *
     * @param  array  $decoded  Raw output from Nip19Helper::decode()
     * @return array Transformed result in {type: string, data: mixed} format
     */
    private function transformDecodeResult(array $decoded): array
    {
        // Handle indexed array format (npub, nsec)
        if (isset($decoded[0]) && is_string($decoded[0])) {
            $type = $decoded[0]; // 'npub', 'nsec', etc.
            $data = $decoded[1] ?? null; // byte array or other data

            // Convert byte array to hex string for consistency
            if (is_array($data)) {
                $hexData = '';
                foreach ($data as $byte) {
                    $hexData .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT);
                }
                $data = $hexData;
            }

            return [
                'type' => $type,
                'data' => $data,
            ];
        }

        // Handle associative array format (note, nprofile, nevent, naddr)
        if (isset($decoded['event_id'])) {
            return [
                'type' => 'note',
                'data' => $decoded['event_id'],
            ];
        }

        // Handle other TLV-parsed formats (nprofile, nevent, naddr)
        // For now, we'll try to detect the type based on available keys
        // This is a simplified approach - in a real implementation,
        // we might need to track the original prefix from the decode process
        if (isset($decoded['pubkey'])) {
            return [
                'type' => 'nprofile',
                'data' => $decoded,
            ];
        }

        if (isset($decoded['id']) && isset($decoded['relays'])) {
            return [
                'type' => 'nevent',
                'data' => $decoded,
            ];
        }

        if (isset($decoded['identifier'])) {
            return [
                'type' => 'naddr',
                'data' => $decoded,
            ];
        }

        // Fallback for unknown formats
        return [
            'type' => 'unknown',
            'data' => $decoded,
        ];
    }

    /**
     * Encode note id to NIP-19 note string.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     *
     * $response = Nostr::native()->nip19()->note('...hex-string...');
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'note' => 'note1...',
     * // ]
     * ```
     *
     * @param  string  $id  Event ID in hex format
     * @return \Illuminate\Http\Client\Response JSON: {note: string}
     *
     * @throws Bech32Exception|BindingResolutionException
     */
    public function note(string $id): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        return $this->response(['note' => $nip19->encodeNote($id)]);
    }

    /**
     * Encode profile to NIP-19 nprofile string.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Nip19\ProfilePointer;
     *
     * $profile = ProfilePointer::make('...hex-string...', ['wss://relay.example.com']);
     * $response = Nostr::native()->nip19()->nprofile($profile);
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'nprofile' => 'nprofile1...',
     * // ]
     * ```
     *
     * @param  ProfilePointer  $profile  Profile pointer containing pubkey and optional relays
     * @return \Illuminate\Http\Client\Response JSON: {nprofile: string}
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function nprofile(ProfilePointer $profile): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        $profileObj = new Profile;
        $profileObj->setPublicKey($profile->toArray()['pubkey']);

        return $this->response(['nprofile' => $nip19->encodeProfile($profileObj, $profile->toArray()['relays'] ?? [])]);
    }

    /**
     * Encode event to NIP-19 nevent string.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Nip19\EventPointer;
     *
     * $event = EventPointer::make('...hex-string...', ['wss://relay.example.com'], '...hex-string...');
     * $response = Nostr::native()->nip19()->nevent($event);
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'nevent' => 'nevent1...',
     * // ]
     * ```
     *
     * @param  EventPointer  $event  Event pointer containing event ID, optional relays and author
     * @return \Illuminate\Http\Client\Response JSON: {nevent: string}
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function nevent(EventPointer $event): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        $eventObj = new Event;
        $eventData = $event->toArray();
        $eventObj->setId($eventData['id']);
        if (! empty($eventData['author'])) {
            $eventObj->setPublicKey($eventData['author']);
        }

        return $this->response(['nevent' => $nip19->encodeEvent($eventObj, $eventData['relays'] ?? [], $eventData['author'] ?? '')]);
    }

    /**
     * Encode address to NIP-19 naddr string.
     *
     * Usage example:
     * ```
     * use Revolution\Nostr\Facades\Nostr;
     * use Revolution\Nostr\Nip19\AddressPointer;
     *
     * $addr = AddressPointer::make('test_identifier', '...hex-string...', 30023, ['wss://relay.example.com']);
     * $response = Nostr::native()->nip19()->naddr($addr);
     * $json = $response->json();
     * // Example return value:
     * // [
     * //     'naddr' => 'naddr1...',
     * // ]
     * ```
     *
     * @param  AddressPointer  $addr  Address pointer containing identifier, pubkey, kind and optional relays
     * @return \Illuminate\Http\Client\Response JSON: {naddr: string}
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function naddr(AddressPointer $addr): Response
    {
        $nip19 = Container::getInstance()->make(Nip19Helper::class);

        $eventObj = new Event;
        $addrData = $addr->toArray();
        $eventObj->setPublicKey($addrData['pubkey']);
        $kind = is_object($addrData['kind']) ? $addrData['kind']->value : $addrData['kind'];
        $eventObj->setKind($kind);

        return $this->response(['naddr' => $nip19->encodeAddr($eventObj, $addrData['identifier'], $kind, $addrData['pubkey'], $addrData['relays'] ?? [])]);
    }
}
