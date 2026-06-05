<?php

class ArticleService
{
    public static function publicList($limit = null, $idCategory = null)
    {
        global $pdo;

        $params = [];
        $sql = "
        SELECT a.id_article, a.id_category, a.title, a.content, a.created_at, a.updated_at, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON c.id_category = a.id_category
        WHERE a.status = 'published'
        ";

        if ($idCategory !== null) {
            $sql .= ' AND a.id_category = ?';
            $params[] = $idCategory;
        }

        $sql .= ' ORDER BY a.updated_at DESC';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allForAdmin()
    {
        global $pdo;

        $sql = "
        SELECT a.id_article, a.id_category, a.id_user, a.title, a.content, a.status, a.created_at, a.updated_at, c.name AS category_name, u.username
        FROM articles a
        LEFT JOIN categories c ON c.id_category = a.id_category
        LEFT JOIN users u ON u.id_user = a.id_user
        ORDER BY a.updated_at DESC
        ";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($idArticle)
    {
        global $pdo;

        $stmt = $pdo->prepare('SELECT id_article, id_category, title, content, status FROM articles WHERE id_article = ?');
        $stmt->execute([$idArticle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findPublished($idArticle)
    {
        global $pdo;

        $sql = "
        SELECT a.id_article, a.id_category, a.title, a.content, a.created_at, a.updated_at, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON c.id_category = a.id_category
        WHERE a.id_article = ? AND a.status = 'published'
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idArticle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($idCategory, $idUser, $title, $content, $status)
    {
        global $pdo;

        $stmt = $pdo->prepare('INSERT INTO articles (id_category, id_user, title, content, status) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$idCategory, $idUser, $title, $content, $status]);
    }

    public static function update($idArticle, $idCategory, $title, $content, $status)
    {
        global $pdo;

        $stmt = $pdo->prepare('UPDATE articles SET id_category = ?, title = ?, content = ?, status = ? WHERE id_article = ?');
        return $stmt->execute([$idCategory, $title, $content, $status, $idArticle]);
    }

    public static function delete($idArticle)
    {
        global $pdo;

        $stmt = $pdo->prepare('DELETE FROM articles WHERE id_article = ?');
        return $stmt->execute([$idArticle]);
    }
}
