<?php

declare(strict_types=1);

/**
 * Flextype (http://flextype.org)
 * Founded by Sergey Romanenko and maintained by Flextype Community.
 */

namespace Flextype;

use Flextype\Component\Filesystem\Filesystem;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use function array_replace_recursive;

/**
 * Validate images token
 */
function validate_images_token($token) : bool
{
    return Filesystem::has(PATH['project'] . '/tokens/images/' . $token . '/token.yaml');
}

/**
 * Fetch image
 *
 * endpoint: GET /api/images
 *
 * Parameters:
 * path - [REQUIRED] - The file path with valid params for image manipulations.
 *
 * Query:
 * token  - [REQUIRED] - Valid Images API token.
 *
 * Returns:
 * Image file
 */
$app->get('/api/images/{path:.+}', function (Request $request, Response $response, $args) use ($flextype, $api_errors) {
    // Get Query Params
    $query = $request->getQueryParams();

    if (! isset($query['token'])) {
        return $response->withStatus($api_errors['0400']['http_status_code'])
            ->withHeader('Content-Type', 'application/json;charset=' . $flextype->registry->get('flextype.settings.charset'))
            ->write($flextype->json->encode($api_errors['0400']));
    }

    // Set variables
    $token = $query['token'];

    if ($flextype['registry']->get('flextype.settings.api.images.enabled')) {
        // Validate delivery image token
        if (validate_images_token($token)) {
            $delivery_images_token_file_path = PATH['project'] . '/tokens/images/' . $token . '/token.yaml';

            // Set delivery token file
            if ($delivery_images_token_file_data = $flextype['yaml']->decode(Filesystem::read($delivery_images_token_file_path))) {
                if ($delivery_images_token_file_data['state'] === 'disabled' ||
                    ($delivery_images_token_file_data['limit_calls'] !== 0 && $delivery_images_token_file_data['calls'] >= $delivery_images_token_file_data['limit_calls'])) {
                    return $response->withStatus($api_errors['0003']['http_status_code'])
            ->withHeader('Content-Type', 'application/json;charset=' . $flextype->registry->get('flextype.settings.charset'))
            ->write($flextype->json->encode($api_errors['0003']));
                }

                // Update calls counter
                Filesystem::write($delivery_images_token_file_path, $flextype['yaml']->encode(array_replace_recursive($delivery_images_token_file_data, ['calls' => $delivery_images_token_file_data['calls'] + 1])));

                if (Filesystem::has(PATH['project'] . '/uploads/entries/' . $args['path'])) {
                    return $flextype['images']->getImageResponse($args['path'], $_GET);
                }

                return $response
                    ->withStatus($api_errors['0402']['http_status_code'])
            ->withHeader('Content-Type', 'application/json;charset=' . $flextype->registry->get('flextype.settings.charset'))
            ->write($flextype->json->encode($api_errors['0402']));
            }

            return $response
                   ->withStatus($api_errors['0003']['http_status_code'])
            ->withHeader('Content-Type', 'application/json;charset=' . $flextype->registry->get('flextype.settings.charset'))
            ->write($flextype->json->encode($api_errors['0003']));
        }

        return $response
               ->withStatus($api_errors['0003']['http_status_code'])
            ->withHeader('Content-Type', 'application/json;charset=' . $flextype->registry->get('flextype.settings.charset'))
            ->write($flextype->json->encode($api_errors['0003']));
    }

    return $response
           ->withStatus($api_errors['0003']['http_status_code'])
            ->withHeader('Content-Type', 'application/json;charset=' . $flextype->registry->get('flextype.settings.charset'))
            ->write($flextype->json->encode($api_errors['0003']));
});
