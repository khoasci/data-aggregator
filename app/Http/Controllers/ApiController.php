<?php

namespace App\Http\Controllers;

use App\Models\Collections\Artwork;
use App\Http\Transformers\ApiSerializer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Manager;

class ApiController extends Controller
{

    public $manager;

    const LIMIT_MAX = 1000;

    public function respondNotFound($message = 'Not found', $detail = 'The item you requested cannot be found.')
    {
        return response()->error($message, $detail, Response::HTTP_NOT_FOUND);
    }

    public function respondInvalidSyntax($message = 'Invalid syntax', $detail = 'The identifier is invalid.')
    {
        return response()->error($message, $detail, Response::HTTP_BAD_REQUEST);
    }

    public function respondFailure($message = 'Failed request', $detail = 'The request failed.')
    {
        return response()->error($message, $detail, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function respondForbidden($message = 'Forbidden', $detail = 'This request is forbidden.')
    {
        return response()->error($message, $detail, Response::HTTP_FORBIDDEN);
    }

    public function respondTooManyIds($message = 'Invalid number of ids', $detail = 'You have requested too many ids. Please send a smaller amount.')
    {
        return response()->error($message, $detail, Response::HTTP_FORBIDDEN);
    }

    public function respondMethodNotAllowed($message = 'Method not allowed', $detail = 'Method not allowed.')
    {
        return response()->error($message, $detail, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function isUuid($id)
    {

        // We must not be using UUIDv3, since the typical regex wasn't matching
        $uuid = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i';

        if (preg_match($uuid, $id))
        {
            return true;
        }
        return false;
    }

    public function isDscId($id)
    {

        $dscFormat = '/^[a-z]{2,3}-[0-9]+-[0-9]+$/i';

        if (preg_match($dscFormat, $id))
        {
            return true;
        }
        return false;
    }

}
