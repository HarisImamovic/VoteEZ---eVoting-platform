<?php
require_once "BaseService.php";
require_once __DIR__ . "/../dao/AuthDao.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
    private $auth_dao;
    public function __construct() {
        $this->auth_dao = new AuthDao();
        parent::__construct(new AuthDao);
    }

    public function get_user_by_email($email){
        return $this->auth_dao->get_user_by_email($email);
    }

    public function register($entity) {
        if (empty($entity['name']) || empty($entity['email']) || empty($entity['password']) || empty($entity['phone'])) {
            return ['success' => false, 'error' => 'Name, email, password and phone are required.', 'status' => 400];
        }
        if (!filter_var($entity['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format.', 'status' => 422];
        }
        if (!ctype_digit($entity['phone'])) {
            return ['success' => false, 'error' => 'Phone number must contain only digits.', 'status' => 422];
        }
        if (strlen($entity['password']) < 8) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters.', 'status' => 422];
        }
        if ($this->auth_dao->get_user_by_email($entity['email'])) {
            return ['success' => false, 'error' => 'Email already registered.', 'status' => 409];
        }

        $new_user = [
            'name'      => $entity['name'],
            'email'     => $entity['email'],
            'phone'     => $entity['phone'],
            'password'  => password_hash($entity['password'], PASSWORD_BCRYPT),
            'has_voted' => 0,
            'role'      => 'voter',
        ];
        $created = parent::add($new_user);
        unset($created['password']);
        return ['success' => true, 'data' => $created];
    }

    public function login($entity) {
        if (empty($entity['email']) || empty($entity['password']) || empty($entity['phone'])) {
            return ['success' => false, 'error' => 'Email, password and phone are required.', 'status' => 400];
        }

        $user = $this->auth_dao->get_user_by_email($entity['email']);
        if (!$user || !password_verify($entity['password'], $user['password']) || $entity['phone'] != $user['phone']) {
            return ['success' => false, 'error' => 'Invalid credentials.', 'status' => 401];
        }

        unset($user['password']);

        $jwt_payload = [
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24),
        ];

        $token = JWT::encode($jwt_payload, Config::JWT_SECRET(), 'HS256');

        return ['success' => true, 'data' => array_merge($user, ['token' => $token])];
    }
}
