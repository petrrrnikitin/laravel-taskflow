<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version : '1.0.0',
    description : 'Task management platform API',
    title : 'TaskFlow API',
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Local development server',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
abstract class Controller {}
