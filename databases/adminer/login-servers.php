<?php

class AdminerLoginServers extends Adminer\Plugin
{
    protected array $servers;

    public function __construct(array $servers)
    {
        $this->servers = $servers;

        if (isset($_POST['auth']['server'])) {
            $key = $_POST['auth']['server'];
            if (!isset($this->servers[$key])) {
                return;
            }
            $_POST['auth']['driver'] = $this->servers[$key]['driver'];
        }
    }

    public function credentials()
    {
        return [Adminer\idx($this->servers[Adminer\SERVER] ?? [], 'server'), $_GET['username'] ?? '', Adminer\get_password()];
    }

    public function login($login, $password)
    {
        if (!isset($this->servers[Adminer\SERVER])) {
            return false;
        }
    }

    public function loginFormField($name, $heading, $value)
    {
        if ($name === 'driver') {
            return '';
        }

        if ($name === 'server') {
            return $heading . Adminer\html_select('auth[server]', array_keys($this->servers), Adminer\SERVER) . "\n";
        }
    }
}

return new AdminerLoginServers([
    (getenv('ADMINER_SERVER_LABEL') ?: 'Database') => [
        'server' => '127.0.0.1:'.getenv('ADMINER_DATABASE_PORT'),
        'driver' => getenv('ADMINER_DRIVER'),
    ],
]);
