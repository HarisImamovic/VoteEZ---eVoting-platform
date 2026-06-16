<?php
require_once __DIR__ . '/../../data/Roles.php';

/**
 * @OA\Get(
 *      path="/votes",
 *      tags={"votes"},
 *      summary="Return all votes (admin only).",
 *      security={{"APIKey":{}}},
 *      @OA\Response(response=200, description="List of votes.")
 * )
 */
Flight::route("GET /votes", function(){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::vote_service()->get_all());
});

/**
 * @OA\Get(
 *      path="/vote_by_id",
 *      tags={"votes"},
 *      summary="Fetch individual vote by ID (admin only).",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="id", in="query", required=true, @OA\Schema(type="integer")),
 *      @OA\Response(response=200, description="Single vote returned.")
 * )
 */
Flight::route("GET /vote_by_id", function(){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::vote_service()->get_by_id(Flight::request()->query['id']));
});

/**
 * @OA\Get(
 *      path="/vote/{id}",
 *      tags={"votes"},
 *      summary="Fetch individual vote by ID from path (admin only).",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\Response(response=200, description="Single vote returned.")
 * )
 */
Flight::route("GET /vote/@id", function($id){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::vote_service()->get_by_id($id));
});

/**
 * @OA\Get(
 *      path="/votes/{user_id}",
 *      tags={"votes"},
 *      summary="Fetch votes by User ID (admin only).",
 *      security={{"APIKey":{}}},
 *      @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\Response(response=200, description="Vote returned.")
 * )
 */
Flight::route("GET /votes/@user_id", function($user_id){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::vote_service()->get_by_user_id($user_id));
});

/**
 * @OA\Post(
 *     path="/votes/submit",
 *     summary="Submit votes for the authenticated voter.",
 *     tags={"votes"},
 *     security={{"APIKey":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"candidate_ids"},
 *             @OA\Property(property="candidate_ids", type="array", @OA\Items(type="integer"), example={1,2})
 *         )
 *     ),
 *     @OA\Response(response=200, description="Vote submitted successfully."),
 *     @OA\Response(response=400, description="Invalid candidate selection."),
 *     @OA\Response(response=422, description="User has already voted.")
 * )
 */
Flight::route("POST /votes/submit", function(){
    Flight::auth_middleware()->authorizeRole(Roles::VOTER);
    $user = Flight::get('user');
    $request = Flight::request()->data->getData();

    if (empty($request['candidate_ids']) || !is_array($request['candidate_ids'])) {
        Flight::halt(400, 'candidate_ids array is required.');
    }

    $result = Flight::vote_service()->submit_votes($user->id, $request['candidate_ids']);
    if (!$result['success']) {
        Flight::halt($result['status'], $result['error']);
    }
    Flight::json(['message' => 'Your vote has been submitted successfully!']);
});

/**
 * @OA\Post(
 *     path="/vote",
 *     summary="Add a vote record directly (admin only).",
 *     tags={"votes"},
 *     security={{"APIKey":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id","candidate_id"},
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="candidate_id", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Vote has been added.")
 * )
 */
Flight::route("POST /vote", function(){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $request = Flight::request()->data->getData();
    Flight::json([
        'message' => 'Vote has been added!',
        'data'    => Flight::vote_service()->add($request)
    ]);
});

/**
 * @OA\Patch(
 *     path="/vote/{id}",
 *     summary="Edit vote details (admin only).",
 *     tags={"votes"},
 *     security={{"APIKey":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Vote has been updated.")
 * )
 */
Flight::route("PATCH /vote/@id", function($id){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $vote = Flight::request()->data->getData();
    Flight::json([
        'message' => 'Vote has been updated!',
        'data'    => Flight::vote_service()->update($vote, $id, 'id')
    ]);
});

/**
 * @OA\Delete(
 *     path="/vote/{id}",
 *     summary="Delete a vote (admin only).",
 *     tags={"votes"},
 *     security={{"APIKey":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Vote deleted successfully.")
 * )
 */
Flight::route("DELETE /vote/@id", function($id){
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::vote_service()->delete($id);
    Flight::json(['message' => 'Vote has been deleted!']);
});
