<?php

declare(strict_types=1);

namespace Tests\Feature;

use InvalidArgumentException;
use Revolution\Nostr\Crypto\Signature\SchnorrSignature;
use Tests\TestCase;

class SchnorrSignatureTest extends TestCase
{
    private SchnorrSignature $signature;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signature = new SchnorrSignature;
    }

    public function test_sign_message_with_hex_private_key(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';

        $result = $this->signature->sign($privateKey, $message);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('signature', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('publicKey', $result);
        $this->assertEquals($message, $result['message']);
        $this->assertIsString($result['signature']);
        $this->assertIsString($result['publicKey']);
        $this->assertEquals(128, strlen($result['signature'])); // 64 bytes hex = 128 chars
        $this->assertEquals(64, strlen($result['publicKey'])); // 32 bytes hex = 64 chars
    }

    public function test_sign_hex_message(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $hexMessage = hash('sha256', 'hello world');

        $result = $this->signature->sign($privateKey, $hexMessage);

        $this->assertIsArray($result);
        $this->assertEquals($hexMessage, $result['message']);
    }

    public function test_verify_valid_signature(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';

        $result = $this->signature->sign($privateKey, $message);
        $isValid = $this->signature->verify($result['publicKey'], $result['signature'], $message);

        $this->assertTrue($isValid);
    }

    public function test_verify_signature_with_hex_message(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $hexMessage = hash('sha256', 'hello world');

        $result = $this->signature->sign($privateKey, $hexMessage);
        $isValid = $this->signature->verify($result['publicKey'], $result['signature'], $hexMessage);

        $this->assertTrue($isValid);
    }

    public function test_verification_fails_with_wrong_signature(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';

        $result = $this->signature->sign($privateKey, $message);

        // Modify the signature to make it invalid
        $invalidSignature = substr($result['signature'], 0, -2).'00';

        $isValid = $this->signature->verify($result['publicKey'], $invalidSignature, $message);

        $this->assertFalse($isValid);
    }

    public function test_verification_fails_with_wrong_public_key(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $wrongPublicKey = '0000000000000000000000000000000000000000000000000000000000000001';
        $message = 'hello world';

        $result = $this->signature->sign($privateKey, $message);
        $isValid = $this->signature->verify($wrongPublicKey, $result['signature'], $message);

        $this->assertFalse($isValid);
    }

    public function test_verification_fails_with_wrong_message(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';
        $wrongMessage = 'goodbye world';

        $result = $this->signature->sign($privateKey, $message);
        $isValid = $this->signature->verify($result['publicKey'], $result['signature'], $wrongMessage);

        $this->assertFalse($isValid);
    }

    public function test_sign_with_custom_random_k(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';
        $randomK = 'c7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';

        $result = $this->signature->sign($privateKey, $message, $randomK);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('signature', $result);
        $this->assertTrue($this->signature->verify($result['publicKey'], $result['signature'], $message));
    }

    public function test_deterministic_signatures_with_same_random_k(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';
        $randomK = 'c7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';

        $result1 = $this->signature->sign($privateKey, $message, $randomK);
        $result2 = $this->signature->sign($privateKey, $message, $randomK);

        $this->assertEquals($result1['signature'], $result2['signature']);
        $this->assertEquals($result1['publicKey'], $result2['publicKey']);
    }

    public function test_throws_exception_for_invalid_private_key(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Private key must be a hex string');

        $this->signature->sign('invalid_hex', 'hello world');
    }

    public function test_throws_exception_for_invalid_public_key_in_verify(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Public key must be a hex string');

        $signature = '0'.str_repeat('0', 127);
        $this->signature->verify('invalid_hex', $signature, 'hello world');
    }

    public function test_throws_exception_for_invalid_random_k(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Randomness must be a hex string');

        $this->signature->sign($privateKey, 'hello world', 'invalid_hex');
    }

    public function test_handles_empty_message(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = '';

        $result = $this->signature->sign($privateKey, $message);
        $isValid = $this->signature->verify($result['publicKey'], $result['signature'], $message);

        $this->assertTrue($isValid);
    }

    public function test_handles_long_message(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = str_repeat('This is a long message. ', 100);

        $result = $this->signature->sign($privateKey, $message);
        $isValid = $this->signature->verify($result['publicKey'], $result['signature'], $message);

        $this->assertTrue($isValid);
    }

    public function test_handles_unicode_message(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'こんにちは世界 🌍 Hello World! 🚀';

        $result = $this->signature->sign($privateKey, $message);
        $isValid = $this->signature->verify($result['publicKey'], $result['signature'], $message);

        $this->assertTrue($isValid);
    }

    public function test_validates_signature_format(): void
    {
        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';

        $result = $this->signature->sign($privateKey, $message);

        // Signature should be exactly 128 hex characters (64 bytes)
        $this->assertEquals(128, strlen($result['signature']));
        $this->assertTrue(ctype_xdigit($result['signature']));

        // Public key should be exactly 64 hex characters (32 bytes)
        $this->assertEquals(64, strlen($result['publicKey']));
        $this->assertTrue(ctype_xdigit($result['publicKey']));
    }

    public function test_validates_bip340_constants(): void
    {
        $this->assertEquals('BIP0340/challenge', SchnorrSignature::CHALLENGE);
        $this->assertEquals('BIP0340/aux', SchnorrSignature::AUX);
        $this->assertEquals('BIP0340/nonce', SchnorrSignature::NONCE);
    }

    /**
     * Test signature length consistency with original SchnorrSignature implementation.
     * This test reproduces the gmp_hexval bug where signatures may not be exactly 128 characters.
     *
     * To run this test 100 times and check failure rate:
     * ```
     * for i in {1..100}; do vendor/bin/phpunit --filter test_original_schnorr_signature_length_consistency --no-coverage 2>/dev/null | grep -E "(FAILED|OK)" | tail -1; done | grep -c FAILED
     * ```
     */
    public function test_original_schnorr_signature_length_consistency(): void
    {
        // Use the original SchnorrSignature from vendor
        $originalSignature = new \Mdanter\Ecc\Crypto\Signature\SchnorrSignature;

        $privateKey = 'b7e151628aed2a6abf7158809cf4f3c762e7160f38b4da56a784d9045190cfef';
        $message = 'hello world';

        $result = $originalSignature->sign($privateKey, $message);

        // This test will occasionally fail due to the gmp_hexval bug in the original implementation
        // where signatures may be shorter than 128 characters when leading zeros are missing
        $this->assertEquals(128, strlen($result['signature']),
            'Original SchnorrSignature should produce 128 character signatures, but got: '.strlen($result['signature']));
    }
}
