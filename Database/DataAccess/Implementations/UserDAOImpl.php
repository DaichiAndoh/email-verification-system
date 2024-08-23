<?php

namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\UserDAO;
use Database\DatabaseManager;
use Models\User;

class UserDAOImpl implements UserDAO {
    public function create(User $user, string $password): bool {
        if ($user->getId() !== null) throw new \Exception('Cannot create a user with an existing ID. id: ' . $user->getId());

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";

        $result = $mysqli->prepareAndExecute(
            $query,
            'sss',
            [
                $user->getUsername(),
                $user->getEmail(),
                password_hash($password, PASSWORD_DEFAULT) // store the hashed password
            ]
        );

        if (!$result) return false;

        $user->setId($mysqli->insert_id);

        return true;
    }

    public function updateEmailConfirmedAt(User $user): bool {
        if ($user->getId() === null) throw new \Exception('Cannot update a user without ID.');

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "UPDATE users SET email_confirmed_at = 'confirmed' WHERE id = ?";

        $result = $mysqli->prepareAndExecute($query, 'i', [$user->getId()]);

        return $result;
    }

    private function getRawById(int $id): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM users WHERE id = ?";

        $result = $mysqli->prepareAndFetchAll($query, 'i', [$id])[0] ?? null;

        if ($result === null) return null;

        return $result;
    }

    private function getRawByEmail(string $email): ?array {
        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "SELECT * FROM users WHERE email = ?";

        $result = $mysqli->prepareAndFetchAll($query, 's', [$email])[0] ?? null;

        if ($result === null) return null;
        return $result;
    }

    private function rawDataToUser(array $rawData): User{
        return new User(
            username: $rawData['username'],
            email: $rawData['email'],
            id: $rawData['id'],
            email_confirmed_at: $rawData['email_confirmed_at'],
            created_at: $rawData['created_at'],
            updated_at: $rawData['updated_at']
        );
    }

    public function getById(int $id): ?User {
        $userRaw = $this->getRawById($id);
        if($userRaw === null) return null;

        return $this->rawDataToUser($userRaw);
    }

    public function getByEmail(string $email): ?User {
        $userRaw = $this->getRawByEmail($email);
        if($userRaw === null) return null;

        return $this->rawDataToUser($userRaw);
    }

    public function getHashedPasswordById(int $id): ?string {
        return $this->getRawById($id)['password'] ?? null;
    }
}
