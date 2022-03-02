<?php

namespace App\Controllers;

use App\Database;
use App\Exceptions\FormValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Models\Article;
use App\Redirect;
use App\Validation\ArticleFormValidator;
use App\Validation\Errors;
use App\Views\View;
use Doctrine\DBAL\Exception;

class ArticleControllers
{
    /**
     * @throws Exception
     */
    public function index(): View
    {
        //try {
            $conn = Database::connection();
            $sql = "SELECT * FROM articles order by created_at desc";
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery()->fetchAllAssociative();

            $articles = [];
            foreach ($result as $item) {
                $articles[] = new Article(
                    $item['title'],
                    $item['description'],
                    $item['created_at'],
                    $item['user_id'],
                    $item['id']
                );
            }
//        }catch (){
//
//        }



        if (Session::isAuthorized()) {
            return new View('Articles/index', [
                'articles' => $articles,
                'authorized' => true,
                'creator' => $_SESSION["userid"]
            ]);
        } else {
            return new View('Articles/index', [
                'articles' => $articles
            ]);
        }
    }


    /**
     * @throws Exception
     */
    public function show(array $input): View
    {
        $_SESSION['articleId'] = $input;

        try {
        $articleQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id=?')
            ->setParameter(0,(int)$input['id'])
            ->executeQuery()
            ->fetchAssociative();

            if(!$articleQuery){
                throw new ResourceNotFoundException("Article with id {$input['id']} not found");
            }
            $article = new Article(
                $articleQuery['title'],
                $articleQuery['description'],
                $articleQuery['created_at'],
                $articleQuery['user_id'],
                $articleQuery['id']
            );
        }catch (ResourceNotFoundException $exception){
            return new View('404');
        }


        $comments = (new CommentController())->showComments($articleQuery['id']);

        $conn = Database::connection();
        $sql = "SELECT * FROM user_profiles where user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $articleQuery['user_id']);
        $articleUser = $stmt->executeQuery()->fetchAllAssociative()[0];

        $articleName = $articleUser['name'] . ' ' . $articleUser['surname'];

        $conn = Database::connection();
        $sql = "SELECT  sum(likes) FROM likes where article_id = {$articleQuery['id']}";
        $stmt = $conn->prepare($sql);
        $resultLikes = $stmt->executeQuery()->fetchAllAssociative()[0];

        $likes = $resultLikes['sum(likes)'] ?? 0;

        if (Session::isAuthorized()) {
            $conn = Database::connection();
            $sql = "SELECT *  FROM likes where article_id = {$articleQuery['id']} and user_id = {$_SESSION['userid']}";
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery();

            $likeButtons = 0;
            if ($result->rowCount() <= 0) {
                $likeButtons = $_SESSION['userid'];
            }

            return new View('Articles/show', [
                'article' => $article,
                'user' => $_SESSION['userid'],
                'likeButtons' => $likeButtons,
                'userName' => $articleName,
                'likes' => $likes,
                'creator' => $_SESSION["userid"],
                'comments' => $comments,
                'authorized' => true
            ]);
        } else {
            return new View('Articles/show', [
                'article' => $article,
                'userName' => $articleName,
                'likes' => $likes,
                'comments' => $comments,
            ]);
        }
    }


    public function create(): View
    {
        return new View('Articles/create', [
            'errors' => Errors::getAll(),
            'inputs' => $_SESSION['inputs'] ?? []
        ]);
    }


    /**
     * @throws Exception
     */
    public function store(): Redirect
    {
        try{
            $validator = (new ArticleFormValidator($_POST, [
                'title' => ['required', 'min:3'],
                'description' => ['required']
            ]));
            $validator->passes();

            Database::connection()
                ->insert('articles', [
                        'title' => $_POST['title'],
                        'description' => trim($_POST['description']),
                        'user_id' => $_SESSION["userid"]
                    ]);

            return new Redirect('/articles');

        }catch(FormValidationException $exception){

            $_SESSION['errors'] = $validator->getErrors();
            $_SESSION['inputs'] = $_POST;
            return new Redirect('/articles/create');
        }
    }


    /**
     * @throws Exception
     */
    public function delete(array $input): Redirect
    {
        Database::connection()
            ->delete('articles', ['id' => (int)$input['id']]);
        return new Redirect('/articles');
    }


    /**
     * @throws Exception
     */
    public function edit(array $input): View
    {
        $conn = Database::connection();
        $sql = "SELECT * FROM articles where id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $input['id']);
        $result = $stmt->executeQuery()->fetchAllAssociative()[0];

        $article = new Article(
            $result['title'],
            $result['description'],
            $result['created_at'],
            $result['user_id'],
            $result['id']
        );

        return new View('Articles/update', [
            'article' => $article
        ]);

    }


    /**
     * @throws Exception
     */
    public function update(array $input): Redirect
    {
        Database::connection()
            ->update('articles',
                ['title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'user_id' => $_SESSION["userName"] . ' ' . $_SESSION["surName"]],
                ['id' => (int)$input['id']]);
        return new Redirect('/articles/' . $input['id']);
    }


    /**
     * @throws Exception
     */
    public function likes(array $input): Redirect
    {
        $conn = Database::connection();
        $sql = "SELECT * FROM likes where article_id = $input[id] and user_id = $_SESSION[userid]";
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();

        if ($result->rowCount() <= 0) {
            Database::connection()
                ->insert('likes',
                    [
                        'user_id' => $_SESSION["userid"],
                        'article_id' => (int)$input['id'],
                        'likes' => $_POST['liked']
                    ]);
        }
        return new Redirect('/articles/' . $input['id']);
    }
}


