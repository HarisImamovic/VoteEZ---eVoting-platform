<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::group('/auth', function() {

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register new user.",
     *     description="Add a new user to the database.",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         description="Add new user",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "email","phone","password"},
     *                 @OA\Property(property="name", type="string", example="Name"),
     *                 @OA\Property(property="email", type="string", example="demo@gmail.com"),
     *                 @OA\Property(property="phone", type="string", example="062456789"),
     *                 @OA\Property(property="password", type="string", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="User has been added."),
     *     @OA\Response(response=400, description="Missing required fields."),
     *     @OA\Response(response=409, description="Email already registered."),
     *     @OA\Response(response=422, description="Validation error.")
     * )
     */
    Flight::route("POST /register", function () {
        $data = Flight::request()->data->getData();
        $response = Flight::auth_service()->register($data);

        if ($response['success']) {
            Flight::json(['message' => 'User registered successfully', 'data' => $response['data']]);
        } else {
            Flight::halt($response['status'], $response['error']);
        }
    });

    /**
     * @OA\Post(
     *      path="/auth/login",
     *      tags={"auth"},
     *      summary="Login using email, password and phone",
     *      @OA\Response(response=200, description="User data and JWT"),
     *      @OA\Response(response=400, description="Missing required fields."),
     *      @OA\Response(response=401, description="Invalid credentials."),
     *      @OA\RequestBody(
     *          description="Credentials",
     *          @OA\JsonContent(
     *              required={"email","password","phone"},
     *              @OA\Property(property="email", type="string", example="demo@gmail.com"),
     *              @OA\Property(property="password", type="string", example="some_password"),
     *              @OA\Property(property="phone", type="string", example="123456789")
     *          )
     *      )
     * )
     */
    Flight::route('POST /login', function() {
        $data = Flight::request()->data->getData();
        $response = Flight::auth_service()->login($data);

        if ($response['success']) {
            Flight::json(['message' => 'User logged in successfully', 'data' => $response['data']]);
        } else {
            Flight::halt($response['status'], $response['error']);
        }
    });

});
