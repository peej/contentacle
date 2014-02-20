<?php

namespace Contentacle\Services;

class JsonStore {

    private $container;

    function __construct($container)
    {
        $this->container = $container;
    }

    private function getFilename($username)
    {
        return $this->container['repo_dir'].'/'.strtolower($username).'/profile.json';
    }

    public function load($username)
    {
        $profileJsonFilename = $this->getFilename($username);
        if (file_exists($profileJsonFilename)) {
            return json_decode(file_get_contents($profileJsonFilename));
        }
        return (object)array();
    }

    public function save($username, $data)
    {
        if (!is_object($data)) {
            throw new \Exception('Data to store must be an object');
        }
        $profileJsonFilename = $this->getFilename($username);
        if (file_put_contents($profileJsonFilename, json_encode($data))) {
            $emailFilename = $this->container['repo_dir'].'/emails.json';
            $emails = json_decode(file_get_contents($emailFilename), true);
            $emails[$data['email']] = $username;
            return file_put_contents($emailFilename, json_encode($emails, true));
        }
        return false;
    }

    public function emailToUsername($email)
    {
        $usernames = json_decode(file_get_contents($this->container['repo_dir'].'/emails.json'), true);
        return isset($usernames[$email]) ? $usernames[$email] : null;
    }

    public function emailToName($email)
    {
        $username = $this->emailToUsername($email);
        if ($username) {
            return $this->load($username)->name;
        }
    }
}