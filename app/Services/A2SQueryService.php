<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class A2SQueryService
{
    // A2S Query constants
    private const A2S_INFO = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";

    private const A2S_PLAYER = "\xFF\xFF\xFF\xFF\x55";

    private const A2S_RULES = "\xFF\xFF\xFF\xFF\x56";

    private const A2S_CHALLENGE = "\xFF\xFF\xFF\xFF\x41";

    private int $timeout;

    private int $maxRetries;

    public function __construct()
    {
        $this->timeout = site_setting('a2s_query_timeout', 3);
        $this->maxRetries = site_setting('a2s_query_retries', 2);
    }

    /**
     * Query server info using A2S_INFO
     */
    public function queryServerInfo(string $ip, int $port): ?array
    {
        $socket = $this->createSocket($ip, $port);
        if (! $socket) {
            return null;
        }

        try {
            // Send A2S_INFO request
            fwrite($socket, self::A2S_INFO);

            $response = $this->readResponse($socket);
            if (! $response) {
                return null;
            }

            return $this->parseServerInfo($response);
        } catch (Exception $e) {
            Log::error('A2S Query Error: '.$e->getMessage());

            return null;
        } finally {
            fclose($socket);
        }
    }

    /**
     * Query player list using A2S_PLAYER
     */
    public function queryPlayers(string $ip, int $port): ?array
    {
        $socket = $this->createSocket($ip, $port);
        if (! $socket) {
            return null;
        }

        try {
            // First, get challenge number
            fwrite($socket, self::A2S_PLAYER."\xFF\xFF\xFF\xFF");
            $response = $this->readResponse($socket);

            if (! $response || strlen($response) < 9) {
                return null;
            }

            // Check if we got a challenge response
            if (ord($response[4]) === 0x41) {
                $challenge = substr($response, 5, 4);
                fwrite($socket, self::A2S_PLAYER.$challenge);
                $response = $this->readResponse($socket);
            }

            if (! $response) {
                return null;
            }

            return $this->parsePlayers($response);
        } catch (Exception $e) {
            Log::error('A2S Player Query Error: '.$e->getMessage());

            return null;
        } finally {
            fclose($socket);
        }
    }

    /**
     * Query server rules using A2S_RULES
     */
    public function queryRules(string $ip, int $port): ?array
    {
        $socket = $this->createSocket($ip, $port);
        if (! $socket) {
            return null;
        }

        try {
            // First, get challenge number
            fwrite($socket, self::A2S_RULES."\xFF\xFF\xFF\xFF");
            $response = $this->readResponse($socket);

            if (! $response || strlen($response) < 9) {
                return null;
            }

            // Check if we got a challenge response
            if (ord($response[4]) === 0x41) {
                $challenge = substr($response, 5, 4);
                fwrite($socket, self::A2S_RULES.$challenge);
                $response = $this->readResponse($socket);
            }

            if (! $response) {
                return null;
            }

            return $this->parseRules($response);
        } catch (Exception $e) {
            Log::error('A2S Rules Query Error: '.$e->getMessage());

            return null;
        } finally {
            fclose($socket);
        }
    }

    /**
     * Quick ping check - just see if server responds
     */
    public function ping(string $ip, int $port): bool
    {
        $socket = $this->createSocket($ip, $port);
        if (! $socket) {
            return false;
        }

        try {
            fwrite($socket, self::A2S_INFO);
            $response = $this->readResponse($socket);

            return $response !== null && strlen($response) > 0;
        } catch (Exception $e) {
            return false;
        } finally {
            fclose($socket);
        }
    }

    /**
     * Get full server status (combines info + players)
     */
    public function getFullStatus(string $ip, int $port): array
    {
        $info = $this->queryServerInfo($ip, $port);

        return [
            'online' => $info !== null,
            'info' => $info,
            'players' => $info ? $this->queryPlayers($ip, $port) : null,
            'queried_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Create UDP socket
     */
    private function createSocket(string $ip, int $port)
    {
        $socket = @fsockopen("udp://{$ip}", $port, $errno, $errstr, $this->timeout);

        if (! $socket) {
            Log::warning("Failed to create socket to {$ip}:{$port} - {$errstr}");

            return null;
        }

        stream_set_timeout($socket, $this->timeout);

        return $socket;
    }

    /**
     * Read response from socket
     */
    private function readResponse($socket): ?string
    {
        $response = '';

        for ($i = 0; $i < $this->maxRetries; $i++) {
            $data = fread($socket, 4096);

            if ($data === false || $data === '') {
                $meta = stream_get_meta_data($socket);
                if ($meta['timed_out']) {
                    continue;
                }
                break;
            }

            $response .= $data;

            // Check if we have a complete response
            if (strlen($response) >= 4) {
                break;
            }
        }

        return $response ?: null;
    }

    /**
     * Parse A2S_INFO response
     */
    private function parseServerInfo(string $response): ?array
    {
        if (strlen($response) < 6) {
            return null;
        }

        $offset = 4; // Skip header

        // Check response type
        $type = ord($response[$offset]);
        $offset++;

        if ($type === 0x49) {
            // Source Engine response
            return $this->parseSourceInfo($response, $offset);
        } elseif ($type === 0x6D) {
            // GoldSource response (older)
            return $this->parseGoldSourceInfo($response, $offset);
        }

        return null;
    }

    /**
     * Parse Source Engine server info
     */
    private function parseSourceInfo(string $response, int $offset): array
    {
        $info = [];

        // Protocol
        $info['protocol'] = ord($response[$offset]);
        $offset++;

        // Server name
        $info['name'] = $this->readString($response, $offset);

        // Map
        $info['map'] = $this->readString($response, $offset);

        // Folder
        $info['folder'] = $this->readString($response, $offset);

        // Game
        $info['game'] = $this->readString($response, $offset);

        // App ID
        if ($offset + 2 <= strlen($response)) {
            $info['app_id'] = unpack('v', substr($response, $offset, 2))[1];
            $offset += 2;
        }

        // Players
        if ($offset + 1 <= strlen($response)) {
            $info['players'] = ord($response[$offset]);
            $offset++;
        }

        // Max players
        if ($offset + 1 <= strlen($response)) {
            $info['max_players'] = ord($response[$offset]);
            $offset++;
        }

        // Bots
        if ($offset + 1 <= strlen($response)) {
            $info['bots'] = ord($response[$offset]);
            $offset++;
        }

        // Server type
        if ($offset + 1 <= strlen($response)) {
            $info['server_type'] = chr(ord($response[$offset]));
            $offset++;
        }

        // Environment
        if ($offset + 1 <= strlen($response)) {
            $info['environment'] = chr(ord($response[$offset]));
            $offset++;
        }

        // Visibility (password)
        if ($offset + 1 <= strlen($response)) {
            $info['password'] = ord($response[$offset]) === 1;
            $offset++;
        }

        // VAC
        if ($offset + 1 <= strlen($response)) {
            $info['vac'] = ord($response[$offset]) === 1;
            $offset++;
        }

        // Version
        $info['version'] = $this->readString($response, $offset);

        return $info;
    }

    /**
     * Parse GoldSource server info
     */
    private function parseGoldSourceInfo(string $response, int $offset): array
    {
        $info = [];

        // Address
        $info['address'] = $this->readString($response, $offset);

        // Name
        $info['name'] = $this->readString($response, $offset);

        // Map
        $info['map'] = $this->readString($response, $offset);

        // Folder
        $info['folder'] = $this->readString($response, $offset);

        // Game
        $info['game'] = $this->readString($response, $offset);

        // Players
        if ($offset + 1 <= strlen($response)) {
            $info['players'] = ord($response[$offset]);
            $offset++;
        }

        // Max players
        if ($offset + 1 <= strlen($response)) {
            $info['max_players'] = ord($response[$offset]);
            $offset++;
        }

        // Protocol
        if ($offset + 1 <= strlen($response)) {
            $info['protocol'] = ord($response[$offset]);
            $offset++;
        }

        return $info;
    }

    /**
     * Parse A2S_PLAYER response
     */
    private function parsePlayers(string $response): array
    {
        if (strlen($response) < 6) {
            return [];
        }

        $offset = 5; // Skip header and type byte
        $playerCount = ord($response[$offset]);
        $offset++;

        $players = [];
        for ($i = 0; $i < $playerCount && $offset < strlen($response); $i++) {
            $player = [];

            // Index
            $player['index'] = ord($response[$offset]);
            $offset++;

            // Name
            $player['name'] = $this->readString($response, $offset);

            // Score
            if ($offset + 4 <= strlen($response)) {
                $player['score'] = unpack('l', substr($response, $offset, 4))[1];
                $offset += 4;
            }

            // Duration
            if ($offset + 4 <= strlen($response)) {
                $player['duration'] = unpack('f', substr($response, $offset, 4))[1];
                $offset += 4;
            }

            $players[] = $player;
        }

        return $players;
    }

    /**
     * Parse A2S_RULES response
     */
    private function parseRules(string $response): array
    {
        if (strlen($response) < 7) {
            return [];
        }

        $offset = 5; // Skip header and type byte
        $ruleCount = unpack('v', substr($response, $offset, 2))[1];
        $offset += 2;

        $rules = [];
        for ($i = 0; $i < $ruleCount && $offset < strlen($response); $i++) {
            $name = $this->readString($response, $offset);
            $value = $this->readString($response, $offset);
            $rules[$name] = $value;
        }

        return $rules;
    }

    /**
     * Read null-terminated string
     */
    private function readString(string $data, int &$offset): string
    {
        $string = '';
        while ($offset < strlen($data) && $data[$offset] !== "\x00") {
            $string .= $data[$offset];
            $offset++;
        }
        $offset++; // Skip null terminator

        return $string;
    }
}
