<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 5/10/16
 * Time: 17:25
 */

namespace OCA\Passman\Utility;


use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

class NotFoundJSONResponse extends JSONResponse {

	/**
	 * Creates a new json response with a not found status code.
	 * @param array $response_data
	 */
	public function __construct($response_data = []) {
		parent::__construct($response_data, Http::STATUS_NOT_FOUND);
	}
}