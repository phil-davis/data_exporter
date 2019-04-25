<?php
/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Juan Pablo Villafáñez <jvillafanez@solidgeargroup.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license GPL-2.0
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 */
namespace OCA\DataExporter\Extractor\MetadataExtractor;

use OCA\DataExporter\Utilities\Iterators\Nodes\RecursiveNodeIteratorFactory;
use OCA\DataExporter\Model\File;
use OCP\Files\Node;

class FilesMetadataExtractor {
	/** @var RecursiveNodeIteratorFactory  */
	private $iteratorFactory;

	public function __construct(RecursiveNodeIteratorFactory $iteratorFactory) {
		$this->iteratorFactory = $iteratorFactory;
	}

	/**
	 * @param string $userId
	 * @return File[]
	 * @throws \OCP\Files\InvalidPathException
	 * @throws \OCP\Files\NotFoundException
	 */
	public function extract($userId) {
		list($iterator, $baseFolder) = $this->iteratorFactory->getUserFolderParentRecursiveIterator($userId);
		$files = [];

		foreach ($iterator as $node) {
			$nodePath = $node->getPath();
			$relativePath = $baseFolder->getRelativePath($nodePath);

			$file = new File();

			$file->setPath($relativePath);
			$file->setETag($node->getEtag());
			$file->setPermissions($node->getPermissions());

			if ($node->getType() === Node::TYPE_FILE) {
				$file->setType(File::TYPE_FILE);
			} else {
				$file->setType(File::TYPE_FOLDER);
			}

			$files[] = $file;
		}

		return $files;
	}
}