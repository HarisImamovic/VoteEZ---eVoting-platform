<?php
require_once __DIR__ . '/../../data/Roles.php';

//Get all candidates from the database
/**
 * @OA\Get(
 *     path="/candidates",
 *     tags={"candidates"},
 *     summary="Return all candidates from the API.",
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="List of candidates."
 *     )
 * )
 */
Flight::route("GET /candidates", function(){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    Flight::json(Flight::candidate_service()->get_all());
});

//Get a candidate by a specific id: /candidate?id=id
/**
 * @OA\Get(
 *     path="/candidate_by_id",
 *     tags={"candidates"},
 *     summary="Fetch individual candidate by ID.",
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         required=true,
 *         description="Candidate ID",
 *         @OA\Schema(type="integer"),
 *         example=1
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Fetch individual candidate."
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - missing or invalid ID."
 *     )
 * )
 */
Flight::route("GET /candidate_by_id", function(){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    Flight::json(Flight::candidate_service()->get_by_id(Flight::request()->query['id']));
});

//Get a candidate by a specific id: /candidate/id
/**
 * @OA\Get(
 *     path="/candidate/{id}",
 *     tags={"candidates"},
 *     summary="Fetch individual candidate by ID from path.",
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Candidate ID",
 *         @OA\Schema(type="integer"),
 *         example=1
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Fetch individual candidate."
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - missing or invalid ID."
 *     )
 * )
 */
Flight::route("GET /candidate/@id", function($id){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    Flight::json(Flight::candidate_service()->get_by_id($id));
});

//Get candidates from the database by party id
/**
 * @OA\Get(
 *     path="/candidates/{party_id}",
 *     tags={"candidates"},
 *     summary="Fetch candidates by Party ID from path.",
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\Parameter(
 *         name="party_id",
 *         in="path",
 *         required=true,
 *         description="Candidate Party ID",
 *         @OA\Schema(type="integer"),
 *         example=1
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Fetch candidates by Party ID."
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request - missing or invalid ID."
 *     )
 * )
 */
Flight::route("GET /candidates/@party_id",function($party_id){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    Flight::json(Flight::candidate_service()->get_by_party_id($party_id));
});

//Add a candidate to the database

/**
 * @OA\Post(
 *     path="/candidate",
 *     summary="Add a new candidate.",
 *     description="Add a new candidate to the database.",
 *     tags={"candidates"},
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\RequestBody(
 *         description="Add new candidate",
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"party_id", "name", "age", "motto", "vote_count"},
 *                  @OA\Property(
 *                     property="party_id",
 *                     type="integer",
 *                     example="1",
 *                     description="Candidate party id"
 *                 ),
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     example="Candidate",
 *                     description="Candidate name"
 *                 ),
 *                 @OA\Property(
 *                     property="age",
 *                     type="integer",
 *                     example="30",
 *                     description="Candidate age"
 *                 ),
 *                 @OA\Property(
 *                     property="motto",
 *                     type="string",
 *                     example="My motto.",
 *                     description="Candidate motto" 
 *                 ),
 *                     @OA\Property(
 *                     property="vote_count",
 *                     type="integer",
 *                     example="0",
 *                     description="Candidate vote count"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Candidate has been added."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error."
 *     )
 * )
 */
Flight::route("POST /candidate", function(){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    $user = Flight::get('user');
    if($user->role === Roles::ADMIN){
        $request = Flight::request()->data->getData();
        Flight::json([
        'message'=>"Candidate has been added!",
        'data'=>Flight::candidate_service()->add($request)
    ]);
    }
    else{
        Flight::json(['message' => "Only admins have the permission for this operation!"]);
    }
});

//Update a candidate from the database
/**
 * @OA\Patch(
 *     path="/candidate/{id}",
 *     summary="Edit candidate details",
 *     description="Update candidate information using their ID.",
 *     tags={"candidates"},
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Candidate ID",
 *         @OA\Schema(type="integer"),
 *         example=1
 *     ),
 *     @OA\RequestBody(
 *         description="Updated candidate information",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="party_id", type="integer", example="0", description="Candidate Party ID"),
 *             @OA\Property(property="name", type="string", example="Candidate", description="Candidate name"),
 *             @OA\Property(property="age", type="integer", example="30", description="Candidate age"),
 *             @OA\Property(property="motto", type="string", example="My motto.", description="Candidate motto."),
 *             @OA\Property(property="vote_count", type="integer", example="0", description="Candidate vote count."),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Candidate has been edited successfully."
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input data."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error."
 *     )
 * )
 */
Flight::route("PATCH /candidate/@id", function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    $candidate = Flight::request()->data->getData();
    Flight::json([
        'message' => 'Candidate has been updated!',
        'data'    => Flight::candidate_service()->update($candidate, $id, 'id')
    ]);
});

//Delete a candidate from the database
/**
 * @OA\Delete(
 *     path="/candidate/{id}",
 *     summary="Delete a candidate by ID.",
 *     description="Delete a candidate from the database using their ID.",
 *     tags={"candidates"},
 *     security={
 *         {"APIKey": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Candidate ID",
 *         @OA\Schema(type="integer"),
 *         example=1
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Candidate deleted successfully."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error."
 *     )
 * )
 */
Flight::route("DELETE /candidate/@id", function($id){
    Flight::auth_middleware()->authorizeRoles([Roles::VOTER, Roles::ADMIN]);
    $user = Flight::get('user');
    if($user->role === Roles::ADMIN){
        Flight::candidate_service()->delete($id);
        Flight::json(['message'=>"Candidate has been deleted!"]);
    }
    else{
        Flight::json(['message'=>"Only admins have the permission for this operation!"]);
    }
    
});