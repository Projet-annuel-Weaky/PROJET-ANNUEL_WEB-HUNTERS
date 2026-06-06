<?php

class SessionManager
{
    /**
     * Initialize session inactivity handling.
     * - Uses session.gc_maxlifetime as default timeout (seconds)
     * - Destroys session and cookie on timeout and redirects to login
     */
    public static function init($timeoutSeconds = null)
    {
        // derive timeout from php config if not provided
        if ($timeoutSeconds === null) {
            $timeoutSeconds = (int) ini_get('session.gc_maxlifetime') ?: 1800;
        }

        // ensure session is active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $now = time();

        // if last activity is set and exceeded timeout -> destroy session
        if (!empty($_SESSION['last_activity']) && ($now - (int) $_SESSION['last_activity'] > $timeoutSeconds)) {
            // clear session data
            $_SESSION = [];

            // clear session cookie if used
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                $path = isset($params['path']) ? $params['path'] : '/';
                $domain = isset($params['domain']) ? $params['domain'] : '';
                $secure = isset($params['secure']) ? $params['secure'] : false;
                $httponly = isset($params['httponly']) ? $params['httponly'] : false;

                setcookie(session_name(), '', $now - 42000, $path, $domain, $secure, $httponly);
            }

            // destroy session
            @session_unset();
            @session_destroy();

            // avoid redirect loops (allow public pages like login, signup, verify, index)
            $current = basename($_SERVER['PHP_SELF'] ?? '');
            $skip = ['login.php', 'signup.php', 'verify.php', 'index.php', 'logout.php'];

            if (!in_array($current, $skip, true)) {
                // redirect to login with timeout flag
                header('Location: login.php?timeout=1');
                exit;
            }

            return;
        }

        // If user is logged in, refresh last activity timestamp
        if (!empty($_SESSION['id_user'])) {
            $_SESSION['last_activity'] = $now;
        } else {
            // keep session non-persistent for anonymous users
            unset($_SESSION['last_activity']);
        }
    }
}
