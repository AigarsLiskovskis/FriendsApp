<?php

namespace App\Controllers;

use App\Database;
use App\Models\Article;
use App\Redirect;
use App\Views\View;
use Doctrine\DBAL\Exception;

class ArticleControllers
{
    /**
     * @throws Exception
     */
    public function index(): View
    {
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

        if (Session::isAuthorized()) {
            return new View('Articles/indexLogin', [
                'articles' => $articles
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
        $article = $this->getArticle($input['id']);

        if (Session::isAuthorized()) {
            return new View('Articles/show', [
                'article' => $article,
                'editArticle' => 'Edit Article'
            ]);
        } else {
            return new View('Articles/show', [
                'article' => $article,
                'editArticle' => ''
            ]);
        }
    }

    public function create(): View
    {
        return new View('Articles/create');
    }

    /**
     * @throws Exception
     */
    public function store(): Redirect
    {
        Database::connection()
            ->insert('articles',
                [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'user_id' => $_SESSION["userName"] . ' ' . $_SESSION["surName"]
                ]);
        return new Redirect('/articles');
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
        $article = $this->getArticle($input['id']);
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
     * @param $id
     * @return Article
     * @throws Exception
     */
    public function getArticle($id): Article
    {
        $conn = Database::connection();
        $sql = "SELECT * FROM articles where id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $id);
        $result = $stmt->executeQuery()->fetchAllAssociative()[0];

        return new Article(
            $result['title'],
            $result['description'],
            $result['created_at'],
            $result['user_id'],
            $result['id']
        );
    }
}

