<?php

/**
 * Test case for the NotFoundJSONResponse class
 * Date: 8/10/16
 * Time: 18:34
 * @copyright Marcos Zuriaga Miguel 2016
 * @license AGPLv3
 */
use \OCA\Passman\Utility\NotFoundJSONResponse;
use \OCP\AppFramework\Http;
class NotFoundJSONResponseTest extends PHPUnit_Framework_TestCase {
	public function testOnEmptyResponse(){
		$data = new NotFoundJSONResponse();
		$this->assertTrue($data->getStatus() === Http::STATUS_NOT_FOUND);
		$this->assertJsonStringEqualsJsonString('[]', $data->render(), 'Expected empty JSON response');
	}

	public function testOnDataResult(){
		$data = [
			'field' => 'value',
			'boolean' => true,
			'integer' => 21
		];
		$response = new NotFoundJSONResponse($data);
		$this->assertTrue($response->getStatus() === Http::STATUS_NOT_FOUND);
		$this->assertJsonStringEqualsJsonString(json_encode($data), $response->render(), 'Rendered data does not match with expected data');
	}
}