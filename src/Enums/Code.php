<?php

namespace App\Enums;

enum Code: int
{
    case SUCCESS = 200;
    case CREATED = 201;
    case UPDATED = 202;
    case NO_CONTENT = 204;
    case NOT_FOUND = 404;
    case FORBIDDEN = 403;
    case NOT_ACCEPTABLE = 405;
    case NOT_MODIFIED = 406;
    case NOT_VALID = 407;
    case CONFLICT = 409;
    case TOO_MANY_REQUESTS = 429;

}
