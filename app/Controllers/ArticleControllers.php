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
        $articlesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->orderBy('created_at', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();

        $articles = [];
        foreach ($articlesQuery as $item) {
            $articles[] = new Article(
                $item['title'],
                $item['description'],
                $item['created_at'],
                $item['user_id'],
                $item['id']
            );
        }

        if (Session::isAuthorized()) {
            return new View('Articles/index', [
                'articles' => $articles,
                'authorized' => true,
                'userId' => $_SESSION["userid"],
                'userFirstName' => $_SESSION['name']
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
                ->setParameter(0, (int)$input['id'])
                ->executeQuery()
                ->fetchAssociative();

            if (!$articleQuery) {
                throw new ResourceNotFoundException("Article with id {$input['id']} not found");
            }
            $article = new Article(
                $articleQuery['title'],
                $articleQuery['description'],
                $articleQuery['created_at'],
                $articleQuery['user_id'],
                $articleQuery['id']
            );
        } catch (ResourceNotFoundException $exception) {
            return new View('404');
        }

        $comments = (new CommentController())->showComments($articleQuery['id']);

        $ownerQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('user_profiles')
            ->where('user_id=?')
            ->setParameter(0, $articleQuery['user_id'])
            ->executeQuery()
            ->fetchAssociative();

        $articleOwnerName = $ownerQuery['name'] . ' ' . $ownerQuery['surname'];

        $likesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('sum(likes)')
            ->from('likes')
            ->where('article_id=?')
            ->setParameter(0, $articleQuery['id'])
            ->executeQuery()
            ->fetchAssociative();

        $likes = $likesQuery['sum(likes)'] ?? 0;

        if (Session::isAuthorized()) {


            $likesQuery = Database::connection()
                ->createQueryBuilder()
                ->select('id')
                ->from('likes')
                ->where("article_id = $articleQuery[id]")
                ->andWhere("user_id = $_SESSION[userid]")
                ->executeQuery()
                ->fetchAssociative();

            return new View('Articles/show', [
                'userFirstName' => $_SESSION['name'],
                'article' => $article,
                'user' => $_SESSION['userid'],
                'articleOwnerName' => $articleOwnerName,
                'likeButtons' => !$likesQuery,
                'likes' => $likes,
                'comments' => $comments,
                'authorized' => true
            ]);
        } else {
            return new View('Articles/show', [
                'article' => $article,
                'articleOwnerName' => $articleOwnerName,
                'likes' => $likes,
                'comments' => $comments,
            ]);
        }
    }


    public function create(): View
    {
        return new View('Articles/create', [
            'userFirstName' => $_SESSION['name'],
            'errors' => Errors::getAll(),
            'authorized' => true,
            'inputs' => $_SESSION['inputs'] ?? []
        ]);
    }


    /**
     * @throws Exception
     */
    public function store(): Redirect
    {
        try {
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

        } catch (FormValidationException $exception) {

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
        $editQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('articles')
            ->where('id =?')
            ->setParameter(0, $input['id'])
            ->executeQuery()
            ->fetchAssociative();

        $article = new Article(
            $editQuery['title'],
            $editQuery['description'],
            $editQuery['created_at'],
            $editQuery['user_id'],
            $editQuery['id']
        );

        return new View('Articles/update', [
            'article' => $article,
            'userFirstName' => $_SESSION['name'],
            'authorized' => true,
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
                    'user_id' => $_SESSION["userid"]],
                ['id' => (int)$input['id']]);
        return new Redirect('/articles/' . $input['id']);
    }


    /**
     * @throws Exception
     */
    public function likes(array $input): Redirect
    {
        $likesQuery = Database::connection()
            ->createQueryBuilder()
            ->select('id')
            ->from('likes')
            ->where("article_id = $input[id]")
            ->andWhere("user_id = $_SESSION[userid]")
            ->executeQuery()
            ->fetchAssociative();

        if (!$likesQuery) {
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


