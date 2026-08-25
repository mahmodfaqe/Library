<?php

namespace App\Support;

class BackupCipher
{
    /**
     * The header OpenSSL writes in front of a salted file.
     */
    private const MAGIC = 'Salted__';

    /**
     * What `openssl enc -pbkdf2` uses by default, matched exactly.
     */
    private const ITERATIONS = 10000;

    private const DIGEST = 'sha256';

    /**
     * Encrypt a backup so that a copy of it may safely leave the server.
     *
     * The format is the one the openssl command line writes and reads:
     *
     *   openssl enc -d -aes-256-cbc -pbkdf2 -in db.sql.gz.enc -out db.sql.gz \
     *       -pass pass:THE-KEY
     *
     * That is the whole point of choosing it. A backup is restored on the day
     * the server is gone, and on that day this application is gone with it —
     * so nothing about getting the data back may depend on this code, or on
     * PHP, or on knowing what Laravel is.
     */
    public static function encrypt(string $plaintext, string $key): string
    {
        $salt = random_bytes(8);
        [$secret, $iv] = self::derive($key, $salt);

        $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $secret, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            throw new \RuntimeException('The backup could not be encrypted.');
        }

        return self::MAGIC.$salt.$ciphertext;
    }

    public static function decrypt(string $encrypted, string $key): string
    {
        if (! str_starts_with($encrypted, self::MAGIC)) {
            throw new \RuntimeException('This file is not an encrypted backup.');
        }

        $salt = substr($encrypted, 8, 8);
        [$secret, $iv] = self::derive($key, $salt);

        $plaintext = openssl_decrypt(
            substr($encrypted, 16),
            'aes-256-cbc',
            $secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plaintext === false) {
            throw new \RuntimeException('The backup would not decrypt: the key is wrong, or the file is damaged.');
        }

        return $plaintext;
    }

    /**
     * The key the backups are locked with, or null when none is set.
     *
     * It must be kept somewhere that is not the server — a password manager —
     * because a key stored beside the thing it protects protects nothing, and
     * a key lost with the server takes every backup with it.
     */
    public static function key(): ?string
    {
        $key = config('library.backup_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function derive(string $key, string $salt): array
    {
        $material = hash_pbkdf2(self::DIGEST, $key, $salt, self::ITERATIONS, 48, true);

        return [substr($material, 0, 32), substr($material, 32, 16)];
    }
}
