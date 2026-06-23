<?php

class SessionManager
{
    public static function init($timeoutSeconds = null)
    {
        if ($timeoutSeconds === null) {
            $timeoutSeconds = (int) ini_get("session.gc_maxlifetime") ?: 1800;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $now = time();

        if (
            !empty($_SESSION["last_activity"]) &&
            $now - (int) $_SESSION["last_activity"] > $timeoutSeconds
        ) {
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                $path = isset($params["path"]) ? $params["path"] : "/";
                $domain = isset($params["domain"]) ? $params["domain"] : "";
                $secure = isset($params["secure"]) ? $params["secure"] : false;
                $httponly = isset($params["httponly"])
                    ? $params["httponly"]
                    : false;

                setcookie(
                    session_name(),
                    "",
                    $now - 42000,
                    $path,
                    $domain,
                    $secure,
                    $httponly,
                );
            }

            @session_unset();
            @session_destroy();

            $current = basename($_SERVER["PHP_SELF"] ?? "");
            $skip = [
                "login.php",
                "signup.php",
                "verify.php",
                "index.php",
                "logout.php",
            ];

            if (!in_array($current, $skip, true)) {
                header("Location: login.php?timeout=1");
                exit();
            }

            return;
        }

        if (!empty($_SESSION["id_user"])) {
            $_SESSION["last_activity"] = $now;
        } else {
            unset($_SESSION["last_activity"]);
        }
    }
}
