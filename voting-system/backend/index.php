<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Dynamic CORS headers
$allowedOrigins = [
    "http://127.0.0.1:5501",
    "https://stingray-app-4n5gc.ondigitalocean.app",
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} 

header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authentication");

// Respond to preflight OPTIONS request and exit early
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204); // No Content
    exit();
} 
require "./vendor/autoload.php";
require "rest/services/CandidateService.php";
require "rest/services/UserService.php";
require "rest/services/VoteService.php";
require "rest/services/InquiryService.php";
require "rest/services/PartyService.php";
require "rest/services/AuthService.php";
require "middleware/AuthMiddleware.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::map('error', function(Throwable $e) {
    Flight::halt(500, 'An internal server error occurred.');
});

Flight::register('candidate_service', "CandidateService");
Flight::register('user_service', "UserService");
Flight::register('vote_service', "VoteService");
Flight::register('inquiry_service', "InquiryService");
Flight::register('party_service', "PartyService");
Flight::register('auth_service',"AuthService");
Flight::register('auth_middleware', "AuthMiddleware");

Flight::route('/*', function(){
    if(
        strpos(Flight::request()->url, '/auth/login') === 0 ||
        strpos(Flight::request()->url, '/auth/register') === 0
    ) {
        return TRUE;
    } else {
        try{
            $token = Flight::request()->getHeader("Authentication");
            if(Flight::auth_middleware()->verifyToken($token)){
                return TRUE;
            }
        }
        catch (\Exception $e){
            Flight::halt(401, $e->getMessage());
        }
    }
});

require_once __DIR__ . '/rest/routes/CandidateRoutes.php';
require_once __DIR__ . '/rest/routes/UserRoutes.php';
require_once __DIR__ . '/rest/routes/VoteRoutes.php';
require_once __DIR__ . '/rest/routes/InquiryRoutes.php';
require_once __DIR__ . '/rest/routes/PartyRoutes.php';
require_once __DIR__ . '/rest/routes/AuthRoutes.php';

Flight::start();