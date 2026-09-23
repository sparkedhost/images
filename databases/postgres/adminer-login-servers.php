<?php

class AdminerLoginServers extends Adminer\Plugin
{
    protected array $servers;

    public function __construct(array $servers)
    {
        $this->servers = $servers;

        if ($_POST['auth']) {
            $key = $_POST['auth']['server'];
            $_POST['auth']['driver'] = $this->servers[$key]['driver'];
        }
    }

    public function credentials()
    {
        return [Adminer\idx($this->servers[Adminer\SERVER], 'server'), $_GET['username'], Adminer\get_password()];
    }

    public function login($login, $password)
    {
        if (!$this->servers[Adminer\SERVER]) {
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
    'PostgreSQL' => [
        'server' => '127.0.0.1:'.(getenv('ADMINER_DATABASE_PORT') ?: '5432'),
        'driver' => 'pgsql',
    ],
]);
