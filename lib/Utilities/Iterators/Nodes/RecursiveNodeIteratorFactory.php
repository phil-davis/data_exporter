<?php
/**
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
namespace OCA\DataExporter\Utilities\Iterators\Nodes;

use OCP\Files\Folder;
use OCP\Files\IRootFolder;

class RecursiveNodeIteratorFactory {
	/** @var IRootFolder */
	private $rootFolder;

	public function __construct(IRootFolder $rootFolder) {
		$this->rootFolder = $rootFolder;
	}

	/**
	 * Returns an array containing a recursive iterator to iterate over the files of the user as the first
	 * element of the array, and the base Folder node used in the iterator as the second element. Something like:
	 * [RecursiveIteratorIterator, Folder]
	 * It will use a RecursiveIteratorIterator class wrapping a RecursiveNodeIterator class.
	 * This RecursiveNodeIterator will return \OCP\Files\Node elements
	 *
	 * Note that a SkipNodeConditionDifferentStorage is already set in the iterator in order to traverse
	 * only the primary storage
	 *
	 * Consider to use something like:
	 * ```
	 * list($iterator, $baseFolder) = $factory->getUserFolderRecursiveIterator($userId);
	 * ```
	 *
	 * You can traverse the iterator like:
	 * ```
	 * foreach ($iterator as $key => $node) { .... }
	 * ```
	 * Note that the $key will always be the path of the node, the same as $node->getPath()
	 * @param string $userId the id of the user
	 * @param int $mode one of the \RecursiveIteratorIterator constants
	 * @return array a RecursiveIteratorIterator wrapping a RecursiveNodeIterator and the base Folder node
	 * @throws \OC\User\NoUserException (unhandled exception)
	 */
	public function getUserFolderRecursiveIterator($userId, $mode = \RecursiveIteratorIterator::SELF_FIRST) {
		$userFolder = $this->rootFolder->getUserFolder($userId);
		$nodeIterator = new RecursiveNodeIterator($userFolder);
		$conditionDifferentStorage = new SkipNodeConditionDifferentStorage($userFolder->getStorage()->getId());
		$nodeIterator->addSkipCondition($conditionDifferentStorage);
		return [new \RecursiveIteratorIterator($nodeIterator, $mode), $userFolder];
	}

	/**
	 * @param string $userId
	 * @param int $mode
	 * @return array
	 * @throws \OCP\Files\NotFoundException
	 */
	public function getTrashBinRecursiveIterator($userId, $mode = \RecursiveIteratorIterator::SELF_FIRST) {
		$trashBinFolder = $this->rootFolder->getUserFolder($userId)->getParent()->get('/files_trashbin/files');
		if (!$trashBinFolder instanceof Folder) {
			throw new \InvalidArgumentException('Only folders can be passed to iterator');
		}
		$nodeIterator = new RecursiveNodeIterator($trashBinFolder);
		$conditionDifferentStorage = new SkipNodeConditionDifferentStorage($trashBinFolder->getStorage()->getId());
		$nodeIterator->addSkipCondition($conditionDifferentStorage);
		return [new \RecursiveIteratorIterator($nodeIterator, $mode), $trashBinFolder];
	}
}
