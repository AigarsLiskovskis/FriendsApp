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
        $userQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('users')
            ->where('email=?')
            ->setParameter(0, $_POST["email"])
            ->executeQuery()
            ->fetchAssociative();

        if ($userQuery) {
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
        $userQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('users')
            ->where('email=?')
            ->setParameter(0, $_POST["email"])
            ->executeQuery()
            ->fetchAssociative();

        if ($userQuery) {

            $checkPassword = password_verify($_POST['password'], $userQuery['password']);

            if ($checkPassword == false) {
                return new Redirect('/users/message');
            }

            $userProfileQuery = Database::connection()
                ->createQueryBuilder()
                ->select('*')
                ->from('user_profiles')
                ->where('user_id=?')
                ->setParameter(0, $userQuery['id'])
                ->executeQuery()
                ->fetchAssociative();

            $user = new User(
                $userQuery['id'],
                $userProfileQuery['name'],
                $userProfileQuery['surname'],
                $userProfileQuery['birthday'],
                $userQuery['email'],
                $userQuery['created_at'],
            );

            Session::setUser($user->getId(), $user->getName(), $user->getSurname() );

            return new Redirect('/');
        } else {
            return new Redirect('/users/message');
        }
    }

    public function error(): View
    {
        return new View('404');
    }

    public function logout(): Redirect
    {
        unset($_SESSION['userid']);
        return new Redirect('/');
    }
}

