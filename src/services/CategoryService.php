<?php

class CategoryService
{
    public static function all()
    {
        global $pdo;

        $stmt = $pdo->query('SELECT id_category, name, description, created_at, updated_at FROM categories ORDER BY name ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function allWithPublishedCount()
    {
        global $pdo;

        $sql = "
        SELECT c.id_category, c.name, c.description, COUNT(a.id_article) AS total_articles
        FROM categories c
        LEFT JOIN articles a ON a.id_category = c.id_category AND a.status = 'published'
        GROUP BY c.id_category, c.name, c.description
        ORDER BY c.name ASC
        ";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($idCategory)
    {
        global $pdo;

        $stmt = $pdo->prepare('SELECT id_category, name, description FROM categories WHERE id_category = ?');
        $stmt->execute([$idCategory]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($name, $description)
    {
        global $pdo;

        $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        return $stmt->execute([$name, $description]);
    }

    public static function update($idCategory, $name, $description)
    {
        global $pdo;

        $stmt = $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id_category = ?');
        return $stmt->execute([$name, $description, $idCategory]);
    }

    public static function delete($idCategory)
    {
        global $pdo;

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id_category = ?');
        return $stmt->execute([$idCategory]);
    }
}
