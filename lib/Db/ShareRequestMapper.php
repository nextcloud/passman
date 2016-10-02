<?php
/**
 * Created by PhpStorm.
 * User: wolfi
 * Date: 1/10/16
 * Time: 23:15
 */

namespace OCA\Passman\Db;


use OCA\Passman\Utility\Utils;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;

class ShareRequestMapper extends Mapper {
    const TABLE_NAME = 'passman_share_request';

    public function __construct(IDBConnection $db, Utils $utils) {
        parent::__construct($db, self::TABLE_NAME);
        $this->utils = $utils;
    }

    public function createRequest(ShareRequest $request){
        return $this->insert($request);
    }
}