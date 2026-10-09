<?php
// classes/User.php - User database operations and authentication

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Find user by email address using a prepared statement
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID using a prepared statement
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Register a new user with hashed password (no plain text)
     */
    public function register(string $firstName, string $lastName, string $email, string $password): int|false {
        // Securely hash password with bcrypt
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (first_name, last_name, email, password, created_at)
            VALUES (:first_name, :last_name, :email, :password, NOW())
        ");

        $success = $stmt->execute([
            ':first_name' => trim($firstName),
            ':last_name' => trim($lastName),
            ':email' => trim($email),
            ':password' => $hashedPassword
        ]);

        return $success ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Verify credentials against database hash and return user data
     */
    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        // Verify password hash
        if (password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }
}
