<?php
/**
 * Translation helper
 *
 * @param string $key Translation key, like "auth.login"
 * @return string
 */
function l($key)
{
    return LanguageManager::getInstance()->get($key);
}
