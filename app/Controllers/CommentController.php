<?php

namespace App\Controllers;

use App\Database;
use App\Models\Comment;
use App\Redirect;
use Doctrine\DBAL\Exception;

class CommentController
{
    /**
     * @throws Exception
     */
    public function showComments($articleId): array
    {
        $commentQuery = Database::connection()
            ->createQueryBuilder()
            ->select('*')
            ->from('comments')
            ->where('article_id =?')
            ->setParameter(0, $articleId)
            ->orderBy('created_at', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();

        $comments = [];
        foreach ($commentQuery as $item) {
            $comments[] = new Comment(
                $item['id'],
                $item['comment'],
                $item['article_id'],
                $item['user_id'],
                $item['created_at'],
                $item['creator_name']
            );
        }
        return $comments;
    }


    /**
     * @throws Exception
     */
    public function addComment(array $input): Redirect
    {
        var_dump('hello');
        Database::connection()
            ->insert('comments',
                [
                    'comment' => $_POST['comment'],
                    'article_id' => (int)$input['id'],
                    'user_id' => $_SESSION["userid"],
                    'creator_name' => $_SESSION["name"] . " " . $_SESSION["surname"]
                ]);
        return new Redirect('/articles/' . $input['id']);
    }


    /**
     * @throws Exception
     */
    public function deleteComment(array $input): Redirect
    {
        Database::connection()
            ->delete('comments', ['id' => (int)$input['id']]);
        return new Redirect('/articles/' . $_SESSION['articleId']['id']);
    }

}