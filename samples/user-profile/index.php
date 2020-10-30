<?php

/**
 * This file is a part of JsonPolicy project.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

require dirname(__DIR__) . '/../vendor/autoload.php';
require __DIR__ . '/user.php';
require __DIR__ . '/userentity.php';

use JsonPolicy\Manager;

// Defining current user identity object
$me = new User(1, 'me');

$manager = Manager::bootstrap([
    'policies' => [
        file_get_contents(__DIR__  . '/policy.json')
    ],
    'custom_markers' => [
        'IDENTITY' => function($prop) {
            global $me;

            return $me->{$prop};
        }
    ]
]);

/**
 * Checking if current user can edit other user
 *
 * @param UserEntity $entity
 *
 * @return void
 */
function CanEditUserEntity($entity)
{
    global $manager;

    if ($manager->isAllowedTo($entity, 'edit')) {
        echo "Yes. You can edit the user '{$entity->username}'\n";
    } else {
        echo "No. You cannot edit the user '{$entity->username}'\n";
    }
}

CanEditUserEntity(new UserEntity(array(
    'id'       => 1,
    'username' => 'me'
)));

CanEditUserEntity(new UserEntity(array(
    'id'       => 2,
    'username' => 'another-user'
)));