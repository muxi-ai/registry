<?php

declare(strict_types=1);

tiny::helpers(['mailgun']);

/**
 * User Model
 *
 * Handles user authentication, account management, and related operations
 */
class User extends TinyModel
{
    /**
     * Retrieves account information for a user
     *
     * @param null|int|string $id User ID (defaults to current user)
     * @return object Account information object
     */
    public function getAccount(null|int|string $id = null): object
    {
        // Use current user ID if none provided
        $id = $id ?? tiny::user()->id;
        // Fetch account data from database
        $account = tiny::db()->getOne('accounts', ['account_id' => $id]);

        // Return formatted account object with essential information
        return (object) [
            'name' => $account['account_first_name'] . ' ' . $account['account_last_name'],
            'email' => $account['account_login_email'],
        ];
    }

    /**
     * Creates a user session by setting a secure cookie
     *
     * @param int $userId User ID
     * @return void
     */
    public function setSession(int $userId): void
    {
        // Create and save a secure cookie with encrypted user information
        tiny::cookie('user', [
            'hash' => $this->encryptUserHash((string)$userId),
        ])->save();
    }

    /**
     * Gets the user session from the cookie hash
     *
     * @param string $hash The encrypted hash to decrypt
     * @return object|null User object or null if decryption fails
     */
    public function getSession(): ?int
    {
        return $this->decryptUserHash(tiny::cookie('user')->data['hash']);
    }

    /**
     * Destroys the current user session(s)
     *
     * This method removes the user authentication cookie, effectively logging the user out
     * of the application. It's typically called during the logout process.
     *
     * @return void
     */
    public function destroySession(): void
    {
        // Delete the user cookie to terminate the session
        tiny::cookie('user')->destroy();
    }

    /**
     * Decrypts a user hash (without nonce)
     *
     * @param string $hash The encrypted hash to decrypt
     * @return int|null User ID or null if decryption fails
     */
    public function decryptUserHash(string $hash): ?int
    {
        // Decrypt the hash
        $decrypted = tiny::cypher()->decrypt($hash, @$_SERVER['CRYPTO_SECRET']);
        return $decrypted ? (int)$decrypted : null;
    }

    /**
     * Encrypts user information into a secure hash (without nonce)
     *
     * @param string $data User data to encrypt
     * @return string Encrypted hash
     */
    public function encryptUserHash(string $data): string
    {
        return tiny::cypher()->encrypt($data, @$_SERVER['CRYPTO_SECRET']);
    }

    /**
     * Sends an account activation email to a new user
     *
     * This method prepares and sends an email to a newly registered user with a link
     * to activate their account and set up their password. The email is sent in both
     * HTML and plain text formats.
     *
     * @param string $email The recipient's email address
     * @param string $name The recipient's name for personalization
     * @param string $verification_url The URL that the user needs to click to activate their account
     * @return void
     */
    public function sendActivationEmail(string $email, string $name, string $verification_url): void
    {
        // Prepare HTML version of the email with formatting and button
        $htmlMessage = "
            <p>Hi {$name},</p>
            <p>Welcome to MUXI Registry - I'm glad you're here!</p>
            <p>To access your account, please click the link below:</p>
            <p><a href=\"$verification_url\" class=\"button\">MUXI Registry ›</a></p>
            ";

        // Prepare plain text version of the email for clients that don't support HTML
        $textMessage =
            'Hi ' . $name . ',' .
            "\n\nWelcome to MUXI Registry - I'm glad you're here!" .
            "\n\nTo access your account, please click the link below: $verification_url";

        // Apply email templates to both message formats
        $htmlMessage = tiny::emailLayout($htmlMessage, 'layouts/emails/default.html');
        $textMessage = tiny::emailLayout($textMessage, 'layouts/emails/text.html');

        // Send the email using the mailgun service with named parameters
        tiny::mailgun()->sendEmail(
            to_email: $email,
            to_name: $name,
            subject: 'Welcome to MUXI Registry!',
            html: $htmlMessage,
            text: $textMessage,
        );
    }

    /**
     * Retrieves a user record by their email address
     *
     * @param string $email The email address to search for
     * @return array User record as an associative array
     */
    public function findAccountByEmail(string $email): ?array
    {
        // Query the accounts table for a record matching the provided email
        $result = tiny::db()->getOne('users', ['LOWER(github_email)' => strtolower($email)]);
        return $result ? $result : [];
    }

    /**
     * Retrieves a user record by their GitHub ID
     *
     * @param string $ghUserId The GitHub ID to search for
     * @return array User record as an associative array
     */
    public function findUserByGitHubId(int $ghUserId): ?array
    {
        // Query the accounts table for a record matching the provided email
        $result = tiny::db()->getOne('users', ['github_id' => $ghUserId]);
        return $result ? $result : [];
    }

    /**
     * Creates a new user account in the database
     *
     * This method handles the creation of a new user account with standardized
     * formatting and default values. It generates a unique account hash,
     * normalizes user input, and allows for additional custom fields.
     *
     * @param string $email User's email address
     * @param string $first_name User's first name
     * @param string $last_name User's last name
     * @param array $extra_payload Additional fields to include in the account record
     * @return array Complete account data including the newly created account_id
     */
    public function createOrUpdateUser(object $ghUser)
    {
        // Prepare the base account payload with default values
        $payload = [
            'github_id' => $ghUser->id,
            'github_username' => $ghUser->login,
            'registry_username' => $ghUser->login,
            'github_avatar' => str_replace('?v=4', '', trim($ghUser->avatar_url)),
            'github_email' => trim(strtolower($ghUser->email)),
            'github_type' => strtolower(trim($ghUser->type)),
            'github_oauth_token' => tiny::cypher()->encrypt($ghUser->github_oauth_token, @$_SERVER['CRYPTO_SECRET']),
            'first_name' => ucwords(trim($ghUser->first_name)),
            'last_name' => ucwords(trim($ghUser->last_name)),
            'company' => $ghUser->company,
            'bio' => $ghUser->bio,
            'twitter_username' => $ghUser->twitter_username,
            'is_verified' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];
        if ($ghUser->github_installation_id) {
            $payload['github_installation_id'] = $ghUser->github_installation_id;
        }

        // tiny::dd($payload);

        $existingUser = $this->findUserByGitHubId($ghUser->id);
        if ($existingUser) {
            $payload['id'] = $existingUser['id'];
            tiny::db()->update('users', $payload, ['id' => $existingUser['id']]);
            return $existingUser;
        }

        // Insert the new account into the database
        tiny::db()->insert('users', $payload);

        // Add the newly created account ID to the returned data
        $payload['id'] = tiny::db()->lastInsertId();

        return $payload;
    }

    /**
     * Creates a new CLI token for a user
     *
     * @param int $userId The user's ID
     * @param string $name The name of the token
     * @return string The token
     */
    public function createCliToken(int $userId, string $name = 'CLI Token'): string
    {
        $token = 'mxr_' . tiny::nanoId(60);
        $token_hash = tiny::cypher()->encrypt($token, @$_SERVER['CRYPTO_SECRET']);
        tiny::db()->insert('cli_tokens', [
            'user_id' => $userId,
            'token_hash' => $token_hash,
            'name' => $name,
            'expires_at' => '2099-12-31 23:59:59', // never expire
            'created_at' => date('Y-m-d H:i:s'),
            'last_used_at' => null,
        ]);
        return $token;
    }

    /**
     * Retrieves a user by their CLI token
     *
     * @param string $token The token to search for
     * @return array|null The user's data as an associative array or null if not found
     */
    public function getUserByCliToken(string $token): ?array
    {
        $token_hash = tiny::cypher()->encrypt($token, @$_SERVER['CRYPTO_SECRET']);
        $user = tiny::db()->getOneQuery('
            SELECT u.*, c.id as token_id, c.name as token_name
            FROM users u
            LEFT JOIN cli_tokens c ON u.id = c.user_id
            WHERE c.token_hash = ? AND c.expires_at > NOW()
        ', [$token_hash]);

        if (!$user) {
            return null;
        }

        tiny::db()->update('cli_tokens', [
            'last_used_at' => date('Y-m-d H:i:s'),
        ], ['id' => $user['token_id']]);

        return $user;
    }

    /**
     * Retrieves complete user account data by their ID
     *
     * @param int $userId The user's ID
     * @return array|null Complete user account data as an associative array or null if not found
     */
    public function getUserById(int $userId): array
    {
        $result = tiny::db()->getOne('users', ['id' => $userId]);
        return $result ? $result : [];
    }

    /**
     * Retrieves the user's GitHub access token by their ID
     *
     * @param int $userId The user's ID
     * @return string|null The user's GitHub OAuth token or null if not found
     */
    public function getGitHubAccessTokenByUserId(int $userId): ?string
    {
        $result = tiny::db()->getOne('users', ['id' => $userId], 'github_oauth_token');
        return $result ? tiny::cypher()->decrypt($result['github_oauth_token'], @$_SERVER['CRYPTO_SECRET']) : null;
    }
}
