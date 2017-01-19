<?php

namespace OCA\Passman\Middleware;

use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use \OCP\AppFramework\Middleware;
use OCP\IRequest;

class APIMiddleware extends Middleware {

	private $request;

	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	public function afterController($controller, $methodName, Response $response) {
		if($response instanceof JSONResponse){
			$origin = $this->request->server['HTTP_ORIGIN'];
			if($origin) {
				$response->addHeader('Access-Control-Allow-Origin', $origin);
			}
		}
		return parent::afterController($controller, $methodName, $response);
	}
}


