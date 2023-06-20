<?php
/**
 * Nextcloud - passman
 *
 * @copyright Copyright (c) 2023, Timo Triebensky (timo@binsky.org)
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\Passman\Db\Factories;

use OCA\Passman\Db\File;
use OCA\Passman\Utility\Utils;

class FileFactory
{
    public function __construct(private string $userId)
    {
    }

    public function make(
        $filename = '0.png',
        $fileData = '012',
        $size = 3,
        $mimeType = 'text/plain',
        string $guid = null,
        int $created = null
    ): File {
        $file = new File();
        $file->setGuid($guid ?? Utils::GUID());
        $file->setUserId($this->userId);
        $file->setFilename($filename);
        $file->setSize($size);
        $file->setCreated($created ?? Utils::getTime());
        $file->setFileData($fileData);
        $file->setMimetype($mimeType);

        return $file;
    }
}
