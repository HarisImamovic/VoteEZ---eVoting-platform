<?php
require_once __DIR__ . '/../../data/Roles.php';

/**
 * @OA\Get(
 *      path="/users",
 *      tags={"users"},
 *      summary="Return all users from the API.",
 *      security={{"APIKey":{}}},
 *      @OA\Response(response=200, description="List of users.")
 * )
 */
Flight::route("GET /users", function(){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $users = Flight::user_service()->get_all();
    $users = array_map(function($u) { unset($u['password']); return $u; }, $users);
    Flight::json($users);
});

/**
 * @OA\Get(
 *      path="/user_by_id",
 *      tags={"users"},
 *      summary="Fetch individual user by ID.",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer")),
 *      @OA\Response(response=200, description="Fetch individual user.")
 * )
 */
Flight::route("GET /user_by_id", function(){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $user = Flight::user_service()->get_by_id(Flight::request()->query['id']);
    unset($user['password']);
    Flight::json($user);
});

/**
 * @OA\Get(
 *      path="/users/{id}",
 *      tags={"users"},
 *      summary="Fetch individual user by ID from path.",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\Response(response=200, description="Fetch individual user.")
 * )
 */
Flight::route("GET /users/@id", function($id){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    $authenticated = Flight::get('user');
    if ($authenticated->role !== Roles::ADMIN && (int)$id !== (int)$authenticated->id) {
        Flight::halt(403, 'Access denied.');
    }
    $user = Flight::user_service()->get_by_id($id);
    unset($user['password']);
    Flight::json($user);
});

/**
 * @OA\Get(
 *      path="/user/{email}",
 *      tags={"users"},
 *      summary="Fetch individual user by email from path.",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="email", in="path", required=true, @OA\Schema(type="string")),
 *      @OA\Response(response=200, description="Fetch individual user by email.")
 * )
 */
Flight::route("GET /user/@email", function($email){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $user = Flight::user_service()->get_by_email($email);
    unset($user['password']);
    Flight::json($user);
});

/**
 * @OA\Post(
 *     path="/user",
 *     summary="Add a new user (admin only).",
 *     tags={"users"},
 *     security={{"APIKey": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"role","name","email","phone","password","has_voted"},
 *             @OA\Property(property="role", type="string", example="voter"),
 *             @OA\Property(property="name", type="string", example="My name"),
 *             @OA\Property(property="email", type="string", example="email@gmail.com"),
 *             @OA\Property(property="phone", type="string", example="60000000"),
 *             @OA\Property(property="password", type="string", example="password123"),
 *             @OA\Property(property="has_voted", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Response(response=200, description="User has been added."),
 *     @OA\Response(response=422, description="Validation error.")
 * )
 */
Flight::route("POST /user", function(){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $request = Flight::request()->data->getData();

    $allowed = ['name', 'email', 'phone', 'password', 'has_voted', 'role'];
    $request = array_intersect_key($request, array_flip($allowed));

    if (empty($request['email']) || !filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
        Flight::halt(422, 'Valid email is required.');
    }
    if (!empty($request['password'])) {
        $request['password'] = password_hash($request['password'], PASSWORD_BCRYPT);
    }

    $created = Flight::user_service()->add($request);
    unset($created['password']);
    Flight::json(['message' => 'User has been added!', 'data' => $created]);
});

/**
 * @OA\Patch(
 *     path="/user/{id}",
 *     summary="Edit user details (admin only).",
 *     tags={"users"},
 *     security={{"APIKey": {}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="role", type="string", example="voter"),
 *             @OA\Property(property="name", type="string", example="My name"),
 *             @OA\Property(property="email", type="string", example="email@gmail.com"),
 *             @OA\Property(property="phone", type="string", example="060000000"),
 *             @OA\Property(property="password", type="string", example="newpassword"),
 *             @OA\Property(property="has_voted", type="integer", example=0)
 *         )
 *     ),
 *     @OA\Response(response=200, description="User has been updated."),
 *     @OA\Response(response=422, description="Validation error.")
 * )
 */
Flight::route("PATCH /user/@id", function($id){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $user = Flight::request()->data->getData();

    $allowed = ['name', 'email', 'phone', 'password', 'has_voted', 'role'];
    $user = array_intersect_key($user, array_flip($allowed));

    if (isset($user['email']) && !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
        Flight::halt(422, 'Invalid email format.');
    }
    if (!empty($user['password'])) {
        $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);
    } else {
        unset($user['password']);
    }

    $updated = Flight::user_service()->update($user, $id, 'id');
    unset($updated['password']);
    Flight::json(['message' => 'User has been updated!', 'data' => $updated]);
});

/**
 * @OA\Delete(
 *      path="/user/{id}",
 *      tags={"users"},
 *      summary="Delete a user (admin only).",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\Response(response=200, description="User deleted successfully.")
 * )
 */
Flight::route("DELETE /user/@id", function($id){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::user_service()->delete($id);
    Flight::json(['message' => 'User has been deleted!']);
});
