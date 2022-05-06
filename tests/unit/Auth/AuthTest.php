<?php

namespace Appwrite\Tests;

use Appwrite\Auth\Auth;
use Utopia\Database\Document;
use Utopia\Database\Validator\Authorization;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function setUp(): void
    {
    }

    /**
     * Reset Roles
     */
    public function tearDown(): void
    {
        Authorization::cleanRoles();
        Authorization::setRole('role:all');
    }

    public function testCookieName()
    {
        $name = 'cookie-name';

        $this->assertEquals(Auth::setCookieName($name), $name);
        $this->assertEquals(Auth::$cookieName, $name);
    }

    public function testEncodeDecodeSession()
    {
        $id = 'id';
        $secret = 'secret';
        $session = 'eyJpZCI6ImlkIiwic2VjcmV0Ijoic2VjcmV0In0=';

        $this->assertEquals(Auth::encodeSession($id, $secret), $session);
        $this->assertEquals(Auth::decodeSession($session), ['id' => $id, 'secret' => $secret]);
    }
    
    public function testHash()
    {
        $secret = 'secret';
        $this->assertEquals(Auth::hash($secret), '2bb80d537b1da3e38bd30361aa855686bde0eacd7162fef6a25fe97bf527a25b');
    }
    
    public function testPassword()
    {
        /*
        General tests, using pre-defined hashes generated by online tools
        */

        // Bcrypt - Version Y
        $plain = 'secret';
        $hash = '$2y$08$PDbMtV18J1KOBI9tIYabBuyUwBrtXPGhLxCy9pWP6xkldVOKLrLKy';
        $generatedHash = Auth::passwordHash($plain, 'bcrypt');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'bcrypt'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'bcrypt'));

        // Bcrypt - Version A
        $plain = 'test123';
        $hash = '$2a$12$3f2ZaARQ1AmhtQWx2nmQpuXcWfTj1YV2/Hl54e8uKxIzJe3IfwLiu';
        $generatedHash = Auth::passwordHash($plain, 'bcrypt');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'bcrypt'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'bcrypt'));

        // Bcrypt - Cost 5
        $plain = 'hello-world';
        $hash = '$2a$05$IjrtSz6SN7UJ6Sh3l.b5jODEvEG2LMJTPAHIaLWRvlWx7if3VMkFO';
        $generatedHash = Auth::passwordHash($plain, 'bcrypt');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'bcrypt'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'bcrypt'));

        // Bcrypt - Cost 15
        $plain = 'super-secret-password';
        $hash = '$2a$15$DS0ZzbsFZYumH/E4Qj5oeOHnBcM3nCCsCA2m4Goigat/0iMVQC4Na';
        $generatedHash = Auth::passwordHash($plain, 'bcrypt');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'bcrypt'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'bcrypt'));

        // MD5 - Short
        $plain = 'appwrite';
        $hash = '144fa7eaa4904e8ee120651997f70dcc';
        $generatedHash = Auth::passwordHash($plain, 'md5');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'md5'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'md5'));

        // MD5 - Long
        $plain = 'AppwriteIsAwesomeBackendAsAServiceThatIsAlsoOpenSourced';
        $hash = '8410e96cf7ac64e0b84c3f8517a82616';
        $generatedHash = Auth::passwordHash($plain, 'md5');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'md5'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'md5'));

        // PHPass
        $plain = 'pass123';
        $hash = '$P$BVKPmJBZuLch27D4oiMRTEykGLQ9tX0';
        $generatedHash = Auth::passwordHash($plain, 'phpass');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'phpass'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'phpass'));

        // SHA
        $plain = 'developersAreAwesome!';
        $hash = '2455118438cb125354b89bb5888346e9bd23355462c40df393fab514bf2220b5a08e4e2d7b85d7327595a450d0ac965cc6661152a46a157c66d681bed20a4735';
        $generatedHash = Auth::passwordHash($plain, 'sha');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'sha'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'sha'));

        // Argon2
        $plain = 'safe-argon-password';
        $hash = '$argon2id$v=19$m=2048,t=3,p=4$MWc5NWRmc2QxZzU2$41mp7rSgBZ49YxLbbxIac7aRaxfp5/e1G45ckwnK0g8';
        $generatedHash = Auth::passwordHash($plain, 'argon2');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'argon2'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'argon2'));

        // SCryptModified
        // TODO: Add tests

        /*
        Provider-specific tests, ensuring functionality of specific use-cases
        */

        // Provider #1 (Database)
        $plain = 'example-password';
        $hash = '$2a$10$3bIGRWUes86CICsuchGLj.e.BqdCdg2/1Ud9LvBhJr0j7Dze8PBdS';
        $generatedHash = Auth::passwordHash($plain, 'bcrypt');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'bcrypt'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'bcrypt'));

        // Provider #2 (Blog)
        $plain = 'your-password';
        $hash = '$P$BkiNDJTpAWXtpaMhEUhUdrv7M0I1g6.';
        $generatedHash = Auth::passwordHash($plain, 'phpass');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'phpass'));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'phpass'));

        // Provider #2 (Google)
        $plain = 'users-password';
        $hash = 'EPKgfALpS9Tvgr/y1ki7ubY4AEGJeWL3teakrnmOacN4XGiyD00lkzEHgqCQ71wGxoi/zb7Y9a4orOtvMV3/Jw==';
        $salt = '56dFqW+kswqktw==';
        $saltSeparator = 'Bw==';
        $signerKey = 'XyEKE9RcTDeLEsL/RjwPDBv/RqDl8fb3gpYEOQaPihbxf1ZAtSOHCjuAAa7Q3oHpCYhXSN9tizHgVOwn6krflQ==';
        
        $options = [ 'salt' => $salt, 'salt_separator' => $saltSeparator, 'signer_key' => $signerKey ];
        $generatedHash = Auth::passwordHash($plain, 'scrypt_mod', $options);
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'scrypt_mod', $options));
        $this->assertEquals(true, Auth::passwordVerify($plain, $hash, 'scrypt_mod', $options));
    }

    public function testUnknownAlgo() {
        $this->expectExceptionMessage('Hashing algorithm \'md8\' is not supported.');

        // Bcrypt - Cost 5
        $plain = 'whatIsMd8?!?';
        $generatedHash = Auth::passwordHash($plain, 'md8');
        $this->assertEquals(true, Auth::passwordVerify($plain, $generatedHash, 'md8'));
    }
    
    public function testPasswordGenerator()
    {
        $this->assertEquals(\mb_strlen(Auth::passwordGenerator()), 40);
        $this->assertEquals(\mb_strlen(Auth::passwordGenerator(5)), 10);
    }
    
    public function testTokenGenerator()
    {
        $this->assertEquals(\mb_strlen(Auth::tokenGenerator()), 256);
        $this->assertEquals(\mb_strlen(Auth::tokenGenerator(5)), 10);
    }
    
    public function testSessionVerify()
    {
        $secret = 'secret1';
        $hash = Auth::hash($secret);
        $tokens1 = [
            new Document([
                '$id' => 'token1',
                'expire' => time() + 60 * 60 * 24,
                'secret' => $hash,
                'provider' => Auth::SESSION_PROVIDER_EMAIL,
                'providerUid' => 'test@example.com',
            ]),
            new Document([
                '$id' => 'token2',
                'expire' => time() - 60 * 60 * 24,
                'secret' => 'secret2',
                'provider' => Auth::SESSION_PROVIDER_EMAIL,
                'providerUid' => 'test@example.com',
            ]),
        ];

        $tokens2 = [
            new Document([ // Correct secret and type time, wrong expire time
                '$id' => 'token1',
                'expire' => time() - 60 * 60 * 24,
                'secret' => $hash,
                'provider' => Auth::SESSION_PROVIDER_EMAIL,
                'providerUid' => 'test@example.com',
            ]),
            new Document([
                '$id' => 'token2',
                'expire' => time() - 60 * 60 * 24,
                'secret' => 'secret2',
                'provider' => Auth::SESSION_PROVIDER_EMAIL,
                'providerUid' => 'test@example.com',
            ]),
        ];

        $this->assertEquals(Auth::sessionVerify($tokens1, $secret), 'token1');
        $this->assertEquals(Auth::sessionVerify($tokens1, 'false-secret'), false);
        $this->assertEquals(Auth::sessionVerify($tokens2, $secret), false);
        $this->assertEquals(Auth::sessionVerify($tokens2, 'false-secret'), false);
    }

    public function testTokenVerify()
    {
        $secret = 'secret1';
        $hash = Auth::hash($secret);
        $tokens1 = [
            new Document([
                '$id' => 'token1',
                'type' => Auth::TOKEN_TYPE_RECOVERY,
                'expire' => time() + 60 * 60 * 24,
                'secret' => $hash,
            ]),
            new Document([
                '$id' => 'token2',
                'type' => Auth::TOKEN_TYPE_RECOVERY,
                'expire' => time() - 60 * 60 * 24,
                'secret' => 'secret2',
            ]),
        ];

        $tokens2 = [
            new Document([ // Correct secret and type time, wrong expire time
                '$id' => 'token1',
                'type' => Auth::TOKEN_TYPE_RECOVERY,
                'expire' => time() - 60 * 60 * 24,
                'secret' => $hash,
            ]),
            new Document([
                '$id' => 'token2',
                'type' => Auth::TOKEN_TYPE_RECOVERY,
                'expire' => time() - 60 * 60 * 24,
                'secret' => 'secret2',
            ]),
        ];

        $tokens3 = [ // Correct secret and expire time, wrong type
            new Document([
                '$id' => 'token1',
                'type' => Auth::TOKEN_TYPE_INVITE,
                'expire' => time() + 60 * 60 * 24,
                'secret' => $hash,
            ]),
            new Document([
                '$id' => 'token2',
                'type' => Auth::TOKEN_TYPE_RECOVERY,
                'expire' => time() - 60 * 60 * 24,
                'secret' => 'secret2',
            ]),
        ];

        $this->assertEquals(Auth::tokenVerify($tokens1, Auth::TOKEN_TYPE_RECOVERY, $secret), 'token1');
        $this->assertEquals(Auth::tokenVerify($tokens1, Auth::TOKEN_TYPE_RECOVERY, 'false-secret'), false);
        $this->assertEquals(Auth::tokenVerify($tokens2, Auth::TOKEN_TYPE_RECOVERY, $secret), false);
        $this->assertEquals(Auth::tokenVerify($tokens2, Auth::TOKEN_TYPE_RECOVERY, 'false-secret'), false);
        $this->assertEquals(Auth::tokenVerify($tokens3, Auth::TOKEN_TYPE_RECOVERY, $secret), false);
        $this->assertEquals(Auth::tokenVerify($tokens3, Auth::TOKEN_TYPE_RECOVERY, 'false-secret'), false);
    }

    public function testIsPrivilegedUser()
    {
        $this->assertEquals(false, Auth::isPrivilegedUser([]));
        $this->assertEquals(false, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_GUEST]));
        $this->assertEquals(false, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_MEMBER]));
        $this->assertEquals(true, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_ADMIN]));
        $this->assertEquals(true, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_DEVELOPER]));
        $this->assertEquals(true, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_OWNER]));
        $this->assertEquals(false, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_APP]));
        $this->assertEquals(false, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_SYSTEM]));

        $this->assertEquals(false, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_APP, 'role:'.Auth::USER_ROLE_APP]));
        $this->assertEquals(false, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_APP, 'role:'.Auth::USER_ROLE_GUEST]));
        $this->assertEquals(true, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_OWNER, 'role:'.Auth::USER_ROLE_GUEST]));
        $this->assertEquals(true, Auth::isPrivilegedUser(['role:'.Auth::USER_ROLE_OWNER, 'role:'.Auth::USER_ROLE_ADMIN, 'role:'.Auth::USER_ROLE_DEVELOPER]));
    }
    
    public function testIsAppUser()
    {
        $this->assertEquals(false, Auth::isAppUser([]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_GUEST]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_MEMBER]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_ADMIN]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_DEVELOPER]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_OWNER]));
        $this->assertEquals(true, Auth::isAppUser(['role:'.Auth::USER_ROLE_APP]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_SYSTEM]));

        $this->assertEquals(true, Auth::isAppUser(['role:'.Auth::USER_ROLE_APP, 'role:'.Auth::USER_ROLE_APP]));
        $this->assertEquals(true, Auth::isAppUser(['role:'.Auth::USER_ROLE_APP, 'role:'.Auth::USER_ROLE_GUEST]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_OWNER, 'role:'.Auth::USER_ROLE_GUEST]));
        $this->assertEquals(false, Auth::isAppUser(['role:'.Auth::USER_ROLE_OWNER, 'role:'.Auth::USER_ROLE_ADMIN, 'role:'.Auth::USER_ROLE_DEVELOPER]));
    }

    public function testGuestRoles()
    {
        $user = new Document([
            '$id' => ''
        ]);

        $roles = Auth::getRoles($user);
        $this->assertCount(1, $roles);
        $this->assertContains('role:guest', $roles);
    }

    public function testUserRoles()
    {
        $user  = new Document([
            '$id' => '123',
            'memberships' => [
                [
                    'teamId' => 'abc',
                    'roles' => [
                        'administrator',
                        'moderator'
                    ]
                ],
                [
                    'teamId' => 'def',
                    'roles' => [
                        'guest'
                    ]
                ]
            ]
        ]);

        $roles = Auth::getRoles($user);

        $this->assertCount(7, $roles);
        $this->assertContains('role:member', $roles);
        $this->assertContains('user:123', $roles);
        $this->assertContains('team:abc', $roles);
        $this->assertContains('team:abc/administrator', $roles);
        $this->assertContains('team:abc/moderator', $roles);
        $this->assertContains('team:def', $roles);
        $this->assertContains('team:def/guest', $roles);
    }

    public function testPrivilegedUserRoles()
    {
        Authorization::setRole('role:'.Auth::USER_ROLE_OWNER);
        $user  = new Document([
            '$id' => '123',
            'memberships' => [
                [
                    'teamId' => 'abc',
                    'roles' => [
                        'administrator',
                        'moderator'
                    ]
                ],
                [
                    'teamId' => 'def',
                    'roles' => [
                        'guest'
                    ]
                ]
            ]
        ]);

        $roles = Auth::getRoles($user);

        $this->assertCount(5, $roles);
        $this->assertNotContains('role:member', $roles);
        $this->assertNotContains('user:123', $roles);
        $this->assertContains('team:abc', $roles);
        $this->assertContains('team:abc/administrator', $roles);
        $this->assertContains('team:abc/moderator', $roles);
        $this->assertContains('team:def', $roles);
        $this->assertContains('team:def/guest', $roles);
    }

    public function testAppUserRoles()
    {
        Authorization::setRole('role:'.Auth::USER_ROLE_APP);
        $user  = new Document([
            '$id' => '123',
            'memberships' => [
                [
                    'teamId' => 'abc',
                    'roles' => [
                        'administrator',
                        'moderator'
                    ]
                ],
                [
                    'teamId' => 'def',
                    'roles' => [
                        'guest'
                    ]
                ]
            ]
        ]);

        $roles = Auth::getRoles($user);

        $this->assertCount(5, $roles);
        $this->assertNotContains('role:member', $roles);
        $this->assertNotContains('user:123', $roles);
        $this->assertContains('team:abc', $roles);
        $this->assertContains('team:abc/administrator', $roles);
        $this->assertContains('team:abc/moderator', $roles);
        $this->assertContains('team:def', $roles);
        $this->assertContains('team:def/guest', $roles);
    }
}
