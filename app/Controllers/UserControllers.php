<?php


namespace App\Controllers;


use App\Database;
use App\Models\User;
use App\Redirect;
use App\Views\View;
use Doctrine\DBAL\Exception;

class UserControllers
{
    public function signUp(): View
    {
        return new View('Users/signUp');
    }

    /**
     * @throws Exception
     */
    public function register(): Redirect
    {
        $conn = Database::connection();
        $sql = "SELECT * FROM users where email = '$_POST[email]'";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        if ($result->rowCount() > 0) {
            return new Redirect('/users/message');
        } else {
            $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
            Database::connection()
                ->insert('users', ['email' => $_POST['email'], 'password' => $hashedPassword]);
            $userid = Database::connection()->lastInsertId();;
            Database::connection()
                ->insert('user_profiles',
                    [
                        'user_id' => $userid,
                        'name' => $_POST['name'],
                        'surname' => $_POST['surname'],
                        'birthday' => $_POST['birthday']
                    ]);
            return new Redirect('/users');
        }
    }

    public function login(): View
    {
        return new View('Users/login');
    }

    /**
     * @throws Exception
     */
    public function signIn(): Redirect
    {
        $conn = Database::connection();
        $sql = "SELECT * FROM users where email = '$_POST[email]'";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        if ($result->rowCount() > 0) {

            $conn = Database::connection();
            $sql = "SELECT * FROM users where email = '$_POST[email]'";
            $stmt = $conn->prepare($sql);
            $userResult = $stmt->executeQuery()->fetchAllAssociative()[0];

            $checkPassword = password_verify($_POST['password'], $userResult['password']);

            if ($checkPassword == false) {
                $stmt = null;
                return new Redirect('/users/message');
            }

            $conn = Database::connection();
            $sql = "SELECT * FROM user_profiles where user_id = '$userResult[id]'";
            $stmt = $conn->prepare($sql);
            $profileResult = $stmt->executeQuery()->fetchAllAssociative()[0];

            $user = new User(
                $userResult['id'],
                $userResult['email'],
                $userResult['created_at'],
                $profileResult['name'],
                $profileResult['surname'],
                $profileResult['birthday']
            );

            $_SESSION["userid"] = $user->getId();
            $_SESSION["userName"] = $user->getName();
            $_SESSION["surName"] = $user->getSurname();

            return new Redirect('/');
        } else {
            return new Redirect('/users/message');
        }
    }

    public function error(): View
    {
        return new View('Users/message');
    }

    public function logout(): Redirect
    {
        unset($_SESSION['userid']);
        return new Redirect('/');
    }
}

