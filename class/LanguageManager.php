<?php

class LanguageManager
{
    protected string $lang;
    protected string $langPath;
    protected array $loadedGroups = [];
    protected array $common = [];
    protected static ?self $instance = null;

    public function __construct(string $lang = 'en', string $basePath = __DIR__ . '/../lang')
    {
        $this->lang = $lang;
        $this->langPath = rtrim($basePath, '/') . '/' . $this->lang;
        $this->loadCommon();
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            $lang = $_SESSION['lang'] ?? 'bn';
            self::$instance = new self($lang);
        }
        return self::$instance;
    }

    public function get(string $key): string
    {
        // First, check in common
        if (isset($this->common[$key])) {
            return $this->common[$key];
        }

        // Then dot notation (group.key)
        $parts = explode('.', $key);
        if (count($parts) < 2) return $key;

        $group = array_shift($parts);
        if (!isset($this->loadedGroups[$group])) {
            $this->loadedGroups[$group] = $this->loadGroup($group);
        }

        $value = $this->loadedGroups[$group];
        foreach ($parts as $part) {
            if (!isset($value[$part])) return $key;
            $value = $value[$part];
        }

        return is_string($value) ? $value : $key;
    }

    protected function loadGroup(string $group): array
    {
        $file = "{$this->langPath}/{$group}.php";
        return file_exists($file) ? include($file) : [];
    }

    protected function loadCommon(): void
    {
        $file = "{$this->langPath}/common.php";
        $this->common = file_exists($file) ? include($file) : [];
    }

    public function setLang(): void
    {
        if(isset($_SESSION['lang'])){
            $lang =$_SESSION['lang'];
        }if(isset($_COOKIE['lang'])) {
            $lang = $_COOKIE['lang'];
        }
        else {
            $lang = 'bn'; // Default language
        }
        if(isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } 
        if($lang !== 'en' && $lang !== 'bn') {
            $lang = 'bn'; // Fallback to Bangla if invalid
        }
        
        setcookie('lang', $lang, time() + (86400 * 30), '/'); // 30 days
        $_SESSION['lang'] = $lang;
        if(isset($_GET['lang'])){
            header("Location: " . URL);
            exit;
        }
        $this->lang = $lang;
        $this->langPath = dirname($this->langPath) . '/' . $lang;
        $this->loadedGroups = [];
        $this->loadCommon();
    }
}
