<?php

namespace App\Controllers;

use App\Database;
use App\Models\User;
use App\Redirect;
use App\Views\View;
use Doctrine\DBAL\Exception;

class FriendsController
{
    /**
     * @throws Exception
     */
    public function findFriends(): View
    {
        //except logged in user
        $conn = Database::connection();
        $sql = "SELECT * FROM user_profiles where user_id <> {$_SESSION["userid"]}";
        $stmt = $conn->prepare($sql);
        $restOfUsers = $stmt->executeQuery()->fetchAllAssociative();

        //friends
        $conn = Database::connection();
        $sql = "select friend_id from friends where user_id = {$_SESSION["userid"]} and friend_id in
                                                (select user_id from friends where friend_id = {$_SESSION["userid"]})";
        $stmt = $conn->prepare($sql);
        $friends = $stmt->executeQuery()->fetchAllAssociative();

        $users = [];
        if (empty($friends)) {
            foreach ($restOfUsers as $user) {
                $users[] = new User(
                    $user['user_id'],
                    $user['name'],
                    $user['surname'],
                    $user['birthday']
                );
            }
        } else {
            foreach ($restOfUsers as $user) {
                foreach ($friends as $friend) {
                    if ($user['user_id'] != $friend['friend_id']) {
                        $users[] = new User(
                            $user['user_id'],
                            $user['name'],
                            $user['surname'],
                            $user['birthday']
                        );
                    }
                }
            }
        }

        return new View('Friends/findFriends', [
            'users' => $users,
            'userId' => $_SESSION["userid"],
            'userFirstName' => $_SESSION['name'],
            'authorized' => true,
        ]);
    }


    /**
     * @throws Exception
     */
    public function showFriends(): View
    {// Invitations
        $conn = Database::connection();
        $sql = "select friend_id from friends where user_id = {$_SESSION["userid"]} and friend_id not in 
                                                (select user_id from friends where friend_id = {$_SESSION["userid"]})";
        $stmt = $conn->prepare($sql);
        $invitations = $stmt->executeQuery()->fetchAllAssociative();

        $inviters = [];
        foreach ($invitations as $item) {
            var_dump((int)$item['friend_id']);
            $inviterNameQuery = Database::connection()
                ->createQueryBuilder()
                ->select('*')
                ->from('user_profiles')
                ->where('user_id=?')
                ->setParameter(0, (int)$item['friend_id'])
                ->executeQuery()
                ->fetchAssociative();
            $inviters[] = new User(
                $inviterNameQuery['user_id'],
                $inviterNameQuery['name'],
                $inviterNameQuery['surname'],
                $inviterNameQuery['birthday']
            );

        }

        $conn = Database::connection();
        $sql = "select friend_id from friends where user_id = {$_SESSION["userid"]} and friend_id in 
                                                (select user_id from friends where friend_id = {$_SESSION["userid"]})";
        $stmt = $conn->prepare($sql);
        $friendResult = $stmt->executeQuery()->fetchallAssociative();

        if (!empty($friendResult)) {
            $friends = [];
            foreach ($friendResult as $friend) {
                $friendNameQuery = Database::connection()
                    ->createQueryBuilder()
                    ->select('*')
                    ->from('user_profiles')
                    ->where('user_id=?')
                    ->setParameter(0, $friend['friend_id'])
                    ->executeQuery()
                    ->fetchAssociative();
                $friends[] = new User(
                    $friendNameQuery['user_id'],
                    $friendNameQuery['name'],
                    $friendNameQuery['surname'],
                    $friendNameQuery['birthday']
                );
            }

            return new View('Friends/showFriends', [
                'inviters' => $inviters,
                'friends' => $friends,
                'loginUser' => $_SESSION["userid"],
                'userFirstName' => $_SESSION['name'],
                'authorized' => true,

            ]);
        } else {
            return new View('Friends/showFriends', [
                'inviters' => $inviters,
                'loginUser' => $_SESSION["userid"],
                'userFirstName' => $_SESSION['name'],
                'authorized' => true,
            ]);
        }
    }


    /**
     * @throws Exception
     */
    public function inviteFriend(array $input): Redirect
    {
        var_dump('hello');
        Database::connection()
            ->insert('friends',
                [
                    'user_id' => (int)$input['id'],
                    'friend_id' => $_SESSION["userid"]
                ]);
        return new Redirect('/findFriends');
    }


    /**
     * @throws Exception
     */
    public function acceptFriend(array $input): Redirect
    {
        var_dump('hello');
        Database::connection()
            ->insert('friends',
                [
                    'user_id' => (int)$input['id'],
                    'friend_id' => $_SESSION["userid"]
                ]);
        return new Redirect('/showFriends');
    }


    /**
     * @throws Exception
     */
    public function rejectFriend(array $input): Redirect
    {
        Database::connection()
            ->delete('friends',
                ['user_id' => $_SESSION["userid"], 'friend_id' => (int)$input['id']]);
        return new Redirect('/showFriends');
    }


    /**
     * @throws Exception
     */
    public function endFriendship(array $input): Redirect
    {
        Database::connection()
            ->delete('friends',
                ['user_id' => (int)$input['id'], 'friend_id' => $_SESSION["userid"]]);
        Database::connection()
            ->delete('friends',
                ['user_id' => $_SESSION["userid"], 'friend_id' => (int)$input['id']]);
        return new Redirect('/showFriends');
    }
}