<?php

namespace OCA\Passman\Middleware;

use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\IRequest;

class APIMiddleware extends Middleware {

	public function __construct(
		private IRequest $request,
	) {
	}

	public function afterController($controller, $methodName, Response $response) {
		if($response instanceof JSONResponse){
			if(isset($this->request->server['HTTP_ORIGIN'])) {
				$response->addHeader('Access-Control-Allow-Origin', $this->request->server['HTTP_ORIGIN']);
			} else {
                $response->addHeader('Access-Control-Allow-Origin', '*');
            }
		}
		return parent::afterController($controller, $methodName, $response);
	}
}


